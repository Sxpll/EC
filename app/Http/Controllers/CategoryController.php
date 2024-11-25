<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $categories = Category::where('isActive', 1)->whereNull('parent_id')->with('childrenRecursive')->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $categories = Category::where('isActive', 1)->whereNull('parent_id')->with('childrenRecursive')->get();
        return view('categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        try {
            $category = Category::create([
                'name' => $request->name,
                'parent_id' => $request->parent_id,
            ]);

            Log::info('Category created successfully', ['category_id' => $category->id]);

            return response()->json([
                'success' => true,
                'category_id' => $category->id
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating category: ' . $e->getMessage());
            return response()->json(['error' => 'Error creating category: ' . $e->getMessage()], 500);
        }
    }


    public function edit(Category $category)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $categories = Category::active()->whereNull('parent_id')->with('childrenRecursive')->get();
        return view('categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        try {
            $category->update([
                'name' => $request->name,
                'parent_id' => $request->parent_id,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error updating category: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update category.'], 500);
        }
    }


    public function destroy($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        try {
            $category = Category::findOrFail($id);

            foreach ($category->childrenRecursive as $child) {
                $this->destroy($child->id);
            }

            $category->update(['isActive' => 0]);

            $products = $category->products;

            foreach ($products as $product) {
                \DB::table('product_category_history')->insert([
                    'product_id' => $product->id,
                    'category_id' => $category->id,
                    'path' => $this->getCategoryPath($category),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json(['success' => 'Category and its subcategories deactivated successfully.']);
        } catch (\Exception $e) {
            Log::error('Error deactivating category: ' . $e->getMessage());
            return response()->json(['error' => 'Error deactivating category: ' . $e->getMessage()], 500);
        }
    }





    public function updateHierarchy(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'new_parent_id' => 'nullable|exists:categories,id',
        ]);

        try {
            $category = Category::findOrFail($request->input('category_id'));
            $newParentId = $request->input('new_parent_id');

            // Zapobieganie przenoszeniu kategorii do jej potomka
            if ($this->isChildCategory($category->id, $newParentId)) {
                return response()->json(['error' => 'Cannot move category to its own child.'], 400);
            }

            // Aktualizacja parent_id
            $category->update(['parent_id' => $newParentId]);

            return response()->json(['success' => true, 'message' => 'Category hierarchy updated successfully.']);
        } catch (\Exception $e) {
            Log::error('Error updating category hierarchy: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update category hierarchy.'], 500);
        }
    }


    /**
     * Sprawdza, czy dana kategoria jest podkategorią innej kategorii.
     */
    private function isChildCategory($categoryId, $parentId)
    {
        if (!$parentId) {
            return false;
        }

        $parent = Category::find($parentId);

        while ($parent) {
            if ($parent->id === $categoryId) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }





    public function getTree()
    {
        $categories = Category::where('isActive', 1)
            ->whereNull('parent_id')
            ->with(['childrenRecursive' => function ($query) {
                $query->where('isActive', 1);
            }])
            ->get();

        $treeData = $this->buildTree($categories);
        return response()->json($treeData);
    }


    private function buildTree($categories)
    {
        return $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'text' => $category->name,
                'children' => $this->buildTree($category->childrenRecursive)
            ];
        })->toArray();
    }


    public function moveProductsToNewSubcategory(Request $request)
    {
        try {
            Log::info('Received input for moving products:', $request->all());

            $request->validate([
                'parent_category_id' => 'required|exists:categories,id',
                'new_category_id' => 'required|exists:categories,id',
                'product_ids' => 'array',
                'product_ids.*' => 'exists:products,id'
            ]);

            Log::info('Validation passed.');

            $parentCategoryId = $request->input('parent_category_id');
            $newCategoryId = $request->input('new_category_id');
            $productIds = $request->input('product_ids', []);

            Log::info('Moving products', [
                'parent_category_id' => $parentCategoryId,
                'new_category_id' => $newCategoryId,
                'product_ids' => $productIds
            ]);

            foreach ($productIds as $productId) {
                $product = Product::findOrFail($productId);

                if ($product->categories()->where('categories.id', $parentCategoryId)->exists()) {
                    $product->categories()->detach($parentCategoryId);
                    Log::info("Detached product ID {$productId} from category {$parentCategoryId}.");

                    $product->categories()->attach($newCategoryId);
                    Log::info("Attached product ID {$productId} to category {$newCategoryId}.");
                } else {
                    Log::warning("Product ID {$productId} was not assigned to category {$parentCategoryId}, nothing to detach.");
                }
            }

            return response()->json(['success' => 'Products moved successfully.']);
        } catch (\Exception $e) {
            Log::error('Error moving products: ' . $e->getMessage());
            return response()->json(['error' => 'Error moving products: ' . $e->getMessage()], 500);
        }
    }

    public function getProducts($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $products = Product::whereHas('categories', function ($query) use ($id) {
            $query->where('categories.id', $id);
        })->get();

        return response()->json(['products' => $products]);
    }


    private function getCategoryPath($category)
    {
        $path = $category->name;
        while ($category->parent) {
            $category = $category->parent;
            $path = $category->name . ' > ' . $path;
        }
        return $path;
    }

    public function show($id)
    {
        $category = Category::with('childrenRecursive')->findOrFail($id);

        return redirect()->route('products.publicIndex', ['category_id' => $category->id]);
    }
}
