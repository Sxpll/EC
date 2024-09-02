<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category added successfully');
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

        // Sprawdzamy, czy żądanie jest typu AJAX
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

        $category = Category::findOrFail($id);
        $category->update(['isActive' => 0]);

        return response()->json(['success' => 'Category deactivated successfully.']);
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
        // Pobierz wszystkie aktywne kategorie bez rodzica i ich dzieci
        $categories = Category::where('isActive', 1)->whereNull('parent_id')->with('childrenRecursive')->get();

        // Przekształć kategorie na format oczekiwany przez jstree
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
                'children' => $this->buildTree($category->childrenRecursive) // Rekurencyjnie budujemy drzewo
            ];
            $tree[] = $node;
        }
        return $tree;
    }
}
