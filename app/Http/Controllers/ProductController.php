<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductHistory;
use App\Models\ProductImage;
use App\Models\ProductAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;

class ProductController extends Controller
{
    public function index()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $products = Product::with('categories')->get();
        $categories = Category::all();

        return view('products.manage-products', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('childrenRecursive')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'availability' => 'required|string|in:available,available_in_7_days,available_in_14_days,unavailable',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,zip|max:10240',
        ]);

        Log::info('Store Product: Received input', $request->all());

        $product = Product::create($request->only('name', 'description', 'price', 'availability'));

        $selectedCategoryIds = array_map('intval', explode(',', implode(',', $request->input('categories'))));

        $validCategoryIds = Category::whereIn('id', $selectedCategoryIds)
            ->pluck('id')
            ->toArray();

        Log::info('Store Product: Valid categories to sync', $validCategoryIds);

        $product->categories()->sync($validCategoryIds);

        $categoryNames = Category::whereIn('id', $validCategoryIds)->pluck('name')->toArray();
        $categoryNamesString = implode(', ', $categoryNames);

        $this->archiveCategories($product, $validCategoryIds);
        $this->addImagesAndAttachments($request, $product);

        $productData = [
            'name' => $product->name,
            'description' => $product->description,
            'categories' => $categoryNamesString,
            'price' => $product->price,
            'availability' => $product->availability,
        ];

        ProductHistory::create([
            'admin_id' => Auth::user()->id,
            'admin_name' => Auth::user()->name,
            'action' => 'created',
            'product_id' => $product->id,
            'field' => 'Product',
            'old_value' => null,
            'new_value' => json_encode($productData),
        ]);

        Log::info('Store Product: Successfully stored product with ID: ' . $product->id);

        return redirect()->route('products.index')->with('success', 'Product added successfully');
    }


    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            // Walidacja danych wejściowych
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'availability' => 'required|string|in:available,available_in_7_days,available_in_14_days,unavailable',
                'categories' => 'required|array|min:1',
                'categories.*' => 'exists:categories,id',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,zip|max:10240',
            ]);

            Log::info('Update Product: Received input', $request->all());

            // Zapisz stare dane produktu do logów/historii
            $oldProductData = $product->only(['name', 'description', 'price', 'availability']);
            $newProductData = $request->only(['name', 'description', 'price', 'availability']);

            foreach ($newProductData as $field => $newValue) {
                $oldValue = $oldProductData[$field];
                if ($oldValue != $newValue) {
                    ProductHistory::create([
                        'admin_id' => Auth::user()->id,
                        'admin_name' => Auth::user()->name,
                        'action' => 'updated',
                        'product_id' => $product->id,
                        'field' => ucfirst($field),
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                    ]);

                    Log::info("Update Product: Field '$field' updated from '$oldValue' to '$newValue'");
                }
            }

            // Aktualizacja danych produktu
            $product->update($newProductData);

            // Synchronizacja kategorii
            $selectedCategoryIds = $request->input('categories');
            $oldCategories = $product->categories->pluck('id')->toArray();
            $validCategoryIds = Category::whereIn('id', $selectedCategoryIds)->pluck('id')->toArray();

            if (array_diff($validCategoryIds, $oldCategories) || array_diff($oldCategories, $validCategoryIds)) {
                $newCategories = Category::whereIn('id', $validCategoryIds)->pluck('name')->toArray();
                $oldCategoriesNames = Category::whereIn('id', $oldCategories)->pluck('name')->toArray();

                ProductHistory::create([
                    'admin_id' => Auth::user()->id,
                    'admin_name' => Auth::user()->name,
                    'action' => 'updated',
                    'product_id' => $product->id,
                    'field' => 'Categories',
                    'old_value' => implode(', ', $oldCategoriesNames),
                    'new_value' => implode(', ', $newCategories),
                ]);

                Log::info('Update Product: Categories updated from [' . implode(', ', $oldCategoriesNames) . '] to [' . implode(', ', $newCategories) . ']');
                $product->categories()->sync($validCategoryIds);

                // Archiwizacja kategorii, jeśli są nieaktywne
                $this->archiveCategories($product, $validCategoryIds);
            }

            // Dodanie nowych obrazów i załączników
            $this->addImagesAndAttachments($request, $product);

            return redirect()->route('products.index')->with('success', 'Produkt zaktualizowany pomyślnie');
        } catch (\Exception $e) {
            Log::error("Błąd podczas aktualizacji produktu o ID: $id - " . $e->getMessage());
            return redirect()->route('products.index')->with('error', 'Nie udało się zaktualizować produktu');
        }
    }





    public function archiveCategories($product, $leafCategoryIds)
    {
        foreach ($leafCategoryIds as $categoryId) {
            $path = $this->getCategoryPath($categoryId);
            $category = Category::find($categoryId);

            if (!$category->isActive) {
                $exists = \DB::table('product_category_history')
                    ->where('product_id', $product->id)
                    ->where('category_id', $categoryId)
                    ->where('path', $path)
                    ->exists();

                if (!$exists) {
                    \DB::table('product_category_history')->insert([
                        'product_id' => $product->id,
                        'category_id' => $categoryId,
                        'path' => $path,
                        'assigned_at' => now(),
                        'removed_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }




    public function addImagesAndAttachments($request, $product)
    {
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $resizedImage = $this->resizeImage($image, 250, 250);

                ProductImage::create([
                    'product_id' => $product->id,
                    'file_data' => $resizedImage,
                    'mime_type' => $image->getClientMimeType()
                ]);
            }
            Log::info('Store Product: Images added');
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                ProductAttachment::create([
                    'product_id' => $product->id,
                    'file_data' => base64_encode(file_get_contents($attachment->getRealPath())),
                    'mime_type' => $attachment->getClientMimeType(),
                    'file_name' => $attachment->getClientOriginalName()
                ]);
            }
            Log::info('Store Product: Attachments added');
        }
    }


    public function show($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Brak autoryzacji');
        }

        try {
            $product = Product::with(['categories', 'images', 'attachments'])->findOrFail($id);
            $histories = ProductHistory::where('product_id', $id)->get();

            return response()->json([
                'product' => $product,
                'histories' => $histories
            ]);
        } catch (\Exception $e) {
            Log::error("Błąd podczas pobierania szczegółów produktu: " . $e->getMessage());
            return response()->json(['error' => 'Błąd podczas pobierania szczegółów produktu'], 500);
        }
    }




    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            // Save history before change
            ProductHistory::create([
                'admin_id' => Auth::user()->id,
                'admin_name' => Auth::user()->name,
                'action' => 'deactivated',
                'product_id' => $id,
                'field' => 'isActive',
                'old_value' => $product->isActive,
                'new_value' => 0,
            ]);

            
            $product->isActive = 0;
            $product->save();

            return redirect()->route('products.index')->with('success', 'Product has been deactivated successfully.');
        } catch (\Exception $e) {
            Log::error("Failed to deactivate product with ID: $id - " . $e->getMessage());
            return redirect()->route('products.index')->with('error', 'Failed to deactivate product.');
        }
    }



    public function activate($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['isActive' => 1]);
        return response()->json(['success' => true]);
    }

    public function storeImages(Request $request, $id)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        $product = Product::findOrFail($id);

        try {
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $encodedData = base64_encode(file_get_contents($image->getRealPath()));
                    ProductImage::create([
                        'product_id' => $product->id,
                        'file_data' => $encodedData,
                        'mime_type' => $image->getClientMimeType()
                    ]);
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Błąd podczas przesyłania obrazów: " . $e->getMessage());
            return response()->json(['error' => 'Błąd podczas przesyłania obrazów'], 500);
        }
    }
    public function storeAttachments(Request $request, $id)
    {
        $request->validate([
            'attachments.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,zip|max:10240',
        ]);

        $product = Product::findOrFail($id);

        try {
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $attachment) {
                    $encodedData = base64_encode(file_get_contents($attachment->getRealPath()));
                    ProductAttachment::create([
                        'product_id' => $product->id,
                        'file_data' => $encodedData,
                        'mime_type' => $attachment->getClientMimeType(),
                        'file_name' => $attachment->getClientOriginalName()
                    ]);
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Błąd podczas przesyłania załączników: " . $e->getMessage());
            return response()->json(['error' => 'Błąd podczas przesyłania załączników'], 500);
        }
    }


    public function deleteImage($productId, $imageId)
    {
        try {
            $image = ProductImage::where('product_id', $productId)->where('id', $imageId)->firstOrFail();
            $image->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Błąd podczas usuwania obrazu'], 500);
        }
    }

    public function deleteAttachment($productId, $attachmentId)
    {
        try {
            $attachment = ProductAttachment::where('product_id', $productId)->where('id', $attachmentId)->firstOrFail();
            $attachment->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Błąd podczas usuwania załącznika'], 500);
        }
    }
    public function getArchivedCategories($id)
    {
        try {
            $archivedCategories = \DB::table('product_category_history')
                ->join('categories', 'product_category_history.category_id', '=', 'categories.id')
                ->where('product_category_history.product_id', $id)
                ->where('categories.isActive', 0)
                ->select('product_category_history.path')
                ->get();

            return response()->json(['archivedCategories' => $archivedCategories]);
        } catch (\Exception $e) {
            Log::error("Błąd podczas pobierania archiwalnych kategorii dla produktu o ID: $id - " . $e->getMessage());
            return response()->json(['error' => 'Błąd podczas pobierania archiwalnych kategorii'], 500);
        }
    }


    private function getCategoryPath($categoryId)
    {
        $category = Category::find($categoryId);
        $path = [];

        while ($category) {
            array_unshift($path, $category->name);
            $category = $category->parent;
        }

        return implode('\\', $path);
    }

    public function publicIndex(Request $request)
    {
        $page = $request->get('page', 1);


        try {
            $productsQuery = Product::where('isActive', true);


            if ($request->has('search')) {
                $search = $request->input('search');
                $productsQuery = $productsQuery->where('name', 'LIKE', "%{$search}%");
            }


            if ($request->has('category_id')) {
                $categoryId = $request->input('category_id');
                $productsQuery->whereHas('categories', function ($query) use ($categoryId) {
                    $query->where('categories.id', $categoryId);
                });
            }


            if ($request->has('sort_by')) {
                $sortBy = $request->input('sort_by');
                if ($sortBy === 'price_asc') {
                    $productsQuery->orderBy('price', 'asc');
                } elseif ($sortBy === 'price_desc') {
                    $productsQuery->orderBy('price', 'desc');
                } elseif ($sortBy === 'name_asc') {
                    $productsQuery->orderBy('name', 'asc');
                } elseif ($sortBy === 'name_desc') {
                    $productsQuery->orderBy('name', 'desc');
                }
            }


            $products = $productsQuery->paginate(10, ['*'], 'page', $page);


            $hasMorePages = $products->hasMorePages();


            if ($request->expectsJson()) {
                $view = view('partials.products', compact('products'))->render();
                return response()->json([
                    'html' => $view,
                    'hasMore' => $hasMorePages,
                ]);
            }


            $categories = Category::whereNull('parent_id')->with('childrenRecursive')->where('isActive', 1)->get();


            return view('products.publicIndex', compact('products', 'hasMorePages', 'categories'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching product details'], 500);
        }
    }


    public function showProduct($id)
    {
        $product = Product::with(['categories', 'images', 'attachments'])->findOrFail($id);
        return view('products.show', compact('product'));
    }
}
