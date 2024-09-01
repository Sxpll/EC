<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductAttachment;
use App\Models\ProductHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'attachments.*' => 'file|max:10240',
        ]);

        $product = Product::create($request->only('name', 'description'));

        $categories = $request->input('categories');
        $product->categories()->sync($categories);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'file_data' => file_get_contents($image->getRealPath()),
                    'mime_type' => $image->getClientMimeType()
                ]);
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                ProductAttachment::create([
                    'product_id' => $product->id,
                    'file_data' => file_get_contents($attachment->getRealPath()),
                    'mime_type' => $attachment->getClientMimeType(),
                    'file_name' => $attachment->getClientOriginalName()
                ]);
            }
        }

        // Zapisz historię dodawania
        ProductHistory::create([
            'admin_id' => Auth::user()->id,
            'admin_name' => Auth::user()->name,
            'action' => 'created',
            'product_id' => $product->id,
            'field' => 'Product',
            'old_value' => null,
            'new_value' => json_encode($product->toArray()),
        ]);

        return redirect()->route('products.index')->with('success', 'Product added successfully');
    }

    public function show($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        try {
            $product = Product::with('categories')->findOrFail($id);
            $histories = ProductHistory::where('product_id', $id)->get();

            return response()->json([
                'product' => $product,
                'histories' => $histories
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching product details: " . $e->getMessage());
            return response()->json(['error' => 'Error fetching product details'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $oldData = $product->toArray(); // Pobieramy stare dane produktu przed aktualizacją
        $oldCategories = $product->categories->pluck('name', 'id')->toArray(); // Pobieramy stare nazwy kategorii

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'categories' => 'nullable|array',
                'categories.*' => 'exists:categories,id',
            ]);

            // Aktualizacja podstawowych pól
            $product->update($request->only('name', 'description'));

            // Sprawdzanie, czy kategorie zostały zmienione
            if ($request->has('categories')) {
                $newCategoryIds = $request->input('categories');
                $newCategories = Category::whereIn('id', $newCategoryIds)->pluck('name', 'id')->toArray(); // Pobieranie nowych nazw kategorii

                // Porównanie starych i nowych kategorii
                if (array_diff($newCategoryIds, array_keys($oldCategories)) || array_diff(array_keys($oldCategories), $newCategoryIds)) {
                    ProductHistory::create([
                        'admin_id' => Auth::user()->id,
                        'admin_name' => Auth::user()->name,
                        'action' => 'updated',
                        'product_id' => $product->id,
                        'field' => 'categories',
                        'old_value' => json_encode(array_values($oldCategories)), // Nazwy starych kategorii
                        'new_value' => json_encode(array_values($newCategories)), // Nazwy nowych kategorii
                    ]);

                    // Synchronizowanie kategorii
                    $product->categories()->sync($newCategoryIds);
                }
            } else {
                $product->categories()->sync([]); // Usuwanie kategorii, jeśli brak nowych
            }

            $newData = $product->toArray(); // Pobieranie nowych danych produktu po aktualizacji

            // Ignorowanie pól `created_at` i `updated_at`
            unset($oldData['created_at'], $oldData['updated_at']);
            unset($newData['created_at'], $newData['updated_at']);

            // Zapisujemy wszystkie zmiany w historii dla pól, które zostały zmienione
            foreach ($newData as $key => $value) {
                if (isset($oldData[$key]) && $oldData[$key] !== $value) {
                    ProductHistory::create([
                        'admin_id' => Auth::user()->id,
                        'admin_name' => Auth::user()->name,
                        'action' => 'updated',
                        'product_id' => $product->id,
                        'field' => $key, // Zapisujemy nazwę pola, które zostało zmienione
                        'old_value' => $oldData[$key], // Stara wartość pola
                        'new_value' => $value, // Nowa wartość pola
                    ]);
                }
            }

            return redirect()->route('products.index')->with('success', 'Product updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("Validation error: " . json_encode($e->errors()));
            return response()->json(['error' => 'Validation error', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Error updating product: " . $e->getMessage());
            return response()->json(['error' => 'Error updating product'], 500);
        }
    }






    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $oldData = $product->toArray();
            $product->update(['isActive' => false]);

            // Zapisz historię usunięcia
            ProductHistory::create([
                'admin_id' => Auth::user()->id,
                'admin_name' => Auth::user()->name,
                'action' => 'deleted',
                'product_id' => $id,
                'field' => 'Product',
                'old_value' => json_encode($oldData),
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Error deactivating product with ID: $id - " . $e->getMessage());
            return response()->json(['error' => 'Error deactivating product'], 500);
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
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120', // Dodano obsługę SVG, WEBP i zwiększono limit rozmiaru
        ]);

        $product = Product::findOrFail($id);

        try {
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'file_data' => file_get_contents($image->getRealPath()),
                        'mime_type' => $image->getClientMimeType()
                    ]);
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Error uploading images: " . $e->getMessage());
            return response()->json(['error' => 'Error uploading images'], 500);
        }
    }

    public function storeAttachments(Request $request, $id)
    {
        $request->validate([
            'attachments.*' => 'required|file|max:10240', // Obsługuje wszystkie typy plików
        ]);

        $product = Product::findOrFail($id);

        try {
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $attachment) {
                    ProductAttachment::create([
                        'product_id' => $product->id,
                        'file_data' => file_get_contents($attachment->getRealPath()),
                        'mime_type' => $attachment->getClientMimeType(),
                        'file_name' => $attachment->getClientOriginalName()
                    ]);
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Error uploading attachments: " . $e->getMessage());
            return response()->json(['error' => 'Error uploading attachments'], 500);
        }
    }

    public function deleteImage($productId, $imageId)
    {
        try {
            $image = ProductImage::where('product_id', $productId)->where('id', $imageId)->firstOrFail();
            $image->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error deleting image'], 500);
        }
    }

    public function deleteAttachment($productId, $attachmentId)
    {
        try {
            $attachment = ProductAttachment::where('product_id', $productId)
                ->where('id', $attachmentId)
                ->firstOrFail();
            $attachment->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error deleting attachment'], 500);
        }
    }
}
