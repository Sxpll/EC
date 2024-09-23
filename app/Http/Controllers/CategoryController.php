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
            // Tworzenie nowej kategorii
            $category = Category::create([
                'name' => $request->name,
                'parent_id' => $request->parent_id,
            ]);

            Log::info('Category created successfully', ['category_id' => $category->id]);

            // Jeśli żądanie jest typu AJAX, zwróć odpowiedź JSON z ID nowej kategorii
            if ($request->ajax()) {
                return response()->json(['success' => 'Category added successfully', 'category_id' => $category->id]);
            }

            return redirect()->route('categories.index')->with('success', 'Category added successfully');
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
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => 'Category renamed successfully.']);
        }

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        try {
            // Znajdź kategorię
            $category = Category::findOrFail($id);

            // Najpierw rekurencyjnie usuń wszystkie dzieci tej kategorii
            foreach ($category->childrenRecursive as $child) {
                // tu wywoluje sama siebie dla kazdego dziecka
                $this->destroy($child->id);
            }

            // Po usunięciu wszystkich dzieci, dezaktywuj kategorię
            $category->update(['isActive' => 0]);

            // Zapisz historię produktów przed usunięciem
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

            // Zwróć odpowiedź o powodzeniu
            return response()->json(['success' => 'Category and its subcategories deactivated successfully.']);
        } catch (\Exception $e) {
            Log::error('Error deactivating category: ' . $e->getMessage());
            return response()->json(['error' => 'Error deactivating category: ' . $e->getMessage()], 500);
        }
    }




    public function activate($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $category = Category::findOrFail($id);
        $category->update(['isActive' => 1]);

        return redirect()->route('categories.index')->with('success', 'Category activated successfully.');
    }

    public function updateHierarchy(Request $request)
    {
        $categories = $request->input('hierarchy');

        try {
            foreach ($categories as $index => $categoryData) {
                $category = Category::findOrFail($categoryData['id']);
                $category->update([
                    'parent_id' => $categoryData['parent_id'] ?? null,
                    'order' => $index,
                ]);

                if ($categoryData['isActive'] == 0) {
                    $this->deactivateChildren($category); // Dezaktywacja dzieci, jeśli rodzic jest nieaktywny
                }

                if (!empty($categoryData['children'])) {
                    $this->updateChildCategories($categoryData['children'], $category->id);
                }
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    private function updateChildCategories($children, $parentId)
    {
        foreach ($children as $index => $childData) {
            $childCategory = Category::findOrFail($childData['id']);
            $childCategory->update([
                'parent_id' => $parentId,
                'order' => $index,
            ]);

            if (!empty($childData['children'])) {
                $this->updateChildCategories($childData['children'], $childCategory->id);
            }
        }
    }

    public function getTree()
    {
        $categories = Category::where('isActive', 1)->whereNull('parent_id')->with('childrenRecursive')->get();
        $treeData = $this->buildTree($categories);

        return response()->json($treeData);
    }

    private function buildTree($categories)
    {
        $tree = [];
        foreach ($categories as $category) {
            $node = [
                'id' => $category->id,
                'text' => $category->name,
                'children' => $this->buildTree($category->childrenRecursive)
            ];
            $tree[] = $node;
        }
        return $tree;
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

    // private function deactivateChildren(Category $category)
    // {
    //     foreach ($category->childrenRecursive as $child) {
    //         $child->update(['isActive' => 0]);
    //         $this->deactivateChildren($child); // Rekurencyjnie deaktywuj wszystkie dzieci
    //     }
    // }

    private function getCategoryPath($category)
    {
        $path = $category->name;
        while ($category->parent) {
            $category = $category->parent;
            $path = $category->name . ' > ' . $path;
        }
        return $path;
    }
}
