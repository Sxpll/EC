<!-- categories/category-tree.blade.php -->
<ul>
    @foreach ($categories as $category)
    <li>
        <input type="checkbox" name="categories[]" value="{{ $category->id }}"
            @if(in_array($category->id, $selectedCategories ?? [])) checked @endif>
        {{ $category->name }}

        @if ($category->childrenRecursive->count())
        <ul>
            @foreach ($category->childrenRecursive as $childCategory)
            <li>
                <input type="checkbox" name="categories[]" value="{{ $childCategory->id }}"
                    @if(in_array($childCategory->id, $selectedCategories ?? [])) checked @endif>
                {{ $childCategory->name }}

                @if ($childCategory->childrenRecursive->count())
                @include('categories.category-tree', ['categories' => $childCategory->childrenRecursive, 'selectedCategories' => $selectedCategories])
                @endif
            </li>
            @endforeach
        </ul>
        @endif
    </li>
    @endforeach
</ul>
