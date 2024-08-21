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

    $products = Product::where('isActive', true)->with('category')->get();
    $categories = Category::all(); // Pobierz wszystkie kategorie

    return view('products.manage-products', compact('products', 'categories'));
}

    public function show($id)
    {

        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $product = Product::with('category')->findOrFail($id);
        $histories = ProductHistory::where('product_id', $id)->get();
        return response()->json(['product' => $product, 'histories' => $histories]);
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'attachments.*' => 'file|max:10240',
        ]);

        Log::info('Storing new product', $request->all());

        $product = Product::create($request->all());

        Log::info('Product stored successfully: ' . $product->id);

        // Zapis historii tworzenia produktu
        ProductHistory::create([
            'admin_id' => Auth::user()->id,
            'admin_name' => Auth::user()->name,
            'action' => 'created',
            'product_id' => $product->id,
            'field' => 'Product',
            'new_value' => json_encode($product->toArray())
        ]);

        // Przechowywanie zdjęć
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'file_data' => base64_encode(file_get_contents($image)),
                    'mime_type' => $image->getClientMimeType()
                ]);
            }
        }

        // Przechowywanie załączników
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                ProductAttachment::create([
                    'product_id' => $product->id,
                    'file_data' => base64_encode(file_get_contents($attachment)),
                    'mime_type' => $attachment->getClientMimeType(),
                    'file_name' => $attachment->getClientOriginalName()
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product added successfully');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $oldData = $product->toArray(); // Zapisujemy stare wartości

        Log::info('Update request received for product ID: ' . $id, ['request_data' => $request->all()]);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // Aktualizacja produktu
        $product->update($request->except(['_method', '_token']));

        // Zapis historii edycji produktu
        foreach ($request->except(['_method', '_token']) as $key => $value) {
            if (isset($oldData[$key]) && $oldData[$key] != $value) {
                ProductHistory::create([
                    'admin_id' => Auth::user()->id,
                    'admin_name' => Auth::user()->name,
                    'action' => 'updated',
                    'product_id' => $product->id,
                    'field' => $key,
                    'old_value' => $oldData[$key],
                    'new_value' => $value,
                ]);
                Log::info('Field updated', [
                    'field' => $key,
                    'old_value' => $oldData[$key],
                    'new_value' => $value
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $oldData = $product->toArray(); // Zapisujemy dane przed usunięciem

        // Zamiast usuwania, ustawiamy isActive na 0
        $product->update(['isActive' => false]);

        // Zapis historii usuwania produktu
        ProductHistory::create([
            'admin_id' => Auth::user()->id,
            'admin_name' => Auth::user()->name,
            'action' => 'deleted',
            'product_id' => $id,
            'field' => 'Product',
            'old_value' => json_encode($oldData),
        ]);

        Log::info('Product deactivated (soft delete) successfully: ' . $id);
        return redirect()->route('products.index')->with('success', 'Product deactivated successfully');
    }

    public function showImages($id)
    {
        try {
            $product = Product::findOrFail($id);
            $images = $product->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'file_data' => base64_encode($image->file_data),
                    'mime_type' => $image->mime_type
                ];
            });

            Log::info("Fetched images for product ID: $id", $images->toArray());

            return response()->json($images);
        } catch (\Exception $e) {
            Log::error("Error fetching images for product ID: $id - " . $e->getMessage());
            return response()->json(['error' => 'Error fetching images'], 500);
        }
    }

    public function showAttachments($id)
    {
        try {
            $product = Product::findOrFail($id);
            $attachments = $product->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file' => base64_encode($attachment->file_data),
                    'mime_type' => $attachment->mime_type,
                    'file_name' => $attachment->file_name,
                ];
            });

            Log::info("Fetched attachments for product ID: $id");

            return response()->json($attachments);
        } catch (\Exception $e) {
            Log::error("Error fetching attachments for product ID: $id - " . $e->getMessage());
            return response()->json(['error' => 'Error fetching attachments'], 500);
        }
    }

    public function deleteImage($productId, $imageId)
    {
        try {
            $image = ProductImage::where('product_id', $productId)->where('id', $imageId)->firstOrFail();
            $image->delete();

            Log::info("Deleted image with ID: $imageId for product ID: $productId");

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Error deleting image for product ID: $productId - " . $e->getMessage());
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

            Log::info("Deleted attachment with ID: $attachmentId for product ID: $productId");

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Error deleting attachment for product ID: $productId - " . $e->getMessage());
            return response()->json(['error' => 'Error deleting attachment'], 500);
        }
    }

    public function storeImages(Request $request, $id)
    {
        $request->validate([
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = Product::findOrFail($id);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'file_data' => file_get_contents($image),
                    'mime_type' => $image->getClientMimeType()
                ]);
            }
        }

        Log::info("Images stored successfully for product ID: $id");
        return response()->json(['success' => true]);
    }

    public function storeAttachments(Request $request, $id)
    {
        $request->validate([
            'attachments.*' => 'file|max:10240',
        ]);

        $product = Product::findOrFail($id);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                ProductAttachment::create([
                    'product_id' => $product->id,
                    'file_data' => file_get_contents($attachment),
                    'mime_type' => $attachment->getClientMimeType(),
                    'file_name' => $attachment->getClientOriginalName()
                ]);
            }
        }

        Log::info("Attachments stored successfully for product ID: $id");
        return response()->json(['success' => true]);
    }

    public function fetchHistory($id)
{
    $histories = ProductHistory::where('product_id', $id)->get();

    if ($histories->isEmpty()) {
        return response()->json(['error' => 'No history found for this product'], 404);
    }

    return response()->json($histories);
}

}
