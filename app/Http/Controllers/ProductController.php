<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();
        $categories = Category::all();
        return view('products.manage-products', compact('products', 'categories'));
    }

    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return response()->json($product);
    }

    // Formularz do dodania nowego produktu
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

        $product = Product::create($request->all());

        // Przechowywanie zdjęć w bazie danych
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'file_data' => base64_encode(file_get_contents($image)), // Kodowanie base64
                    'mime_type' => $image->getClientMimeType()
                ]);
            }
        }

        // Przechowywanie załączników w bazie danych
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                ProductAttachment::create([
                    'product_id' => $product->id,
                    'file_data' => base64_encode(file_get_contents($attachment)), // Kodowanie base64
                    'mime_type' => $attachment->getClientMimeType(),
                    'file_name' => $attachment->getClientOriginalName()
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product added successfully');
    }

    // Aktualizacja produktu
    public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'category_id' => 'nullable|exists:categories,id', // Kategorie mogą być null
    ]);

    $product->update($request->all());

    // Logowanie zmiany produktu
    // Tutaj możesz dodać logikę zapisu historii zmian

    return response()->json(['success' => true]); // Zwracamy odpowiedź JSON
}

    // Usuwanie produktu
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully');
    }

    public function showImages($id)
    {
        try {
            $product = Product::findOrFail($id);
            $images = $product->images->map(function($image) {
                return [
                    'file_data' => base64_encode($image->file_data), // Zakoduj do base64
                    'mime_type' => $image->mime_type
                ];
            });

            Log::info("Fetched images for product ID: $id");

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
            $attachments = $product->attachments->map(function($attachment) {
                return [
                    'file' => base64_encode($attachment->file_data), // Zakoduj do base64
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

        Log::info("Deleted image ID: $imageId for product ID: $productId");

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        Log::error("Error deleting image ID: $imageId for product ID: $productId - " . $e->getMessage());
        return response()->json(['error' => 'Error deleting image'], 500);
    }
}

public function deleteAttachment($productId, $attachmentId)
{
    try {
        $attachment = ProductAttachment::where('product_id', $productId)->where('id', $attachmentId)->firstOrFail();
        $attachment->delete();

        Log::info("Deleted attachment ID: $attachmentId for product ID: $productId");

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        Log::error("Error deleting attachment ID: $attachmentId for product ID: $productId - " . $e->getMessage());
        return response()->json(['error' => 'Error deleting attachment'], 500);
    }
}

}
