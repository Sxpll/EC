<ul>
    @foreach ($categories as $category)
    <li>
        <input type="checkbox" name="categories[]" value="{{ $category->id }}"
            @if(in_array($category->id, $selectedCategories ?? [])) checked @endif>
        {{ $category->name }}

        @if ($category->childrenRecursive->count())
        <ul>
            @foreach ($category->childrenRecursive as $childCategory)
            @include('categories.category-tree', ['categories' => $childCategory->childrenRecursive, 'selectedCategories' => $selectedCategories ?? []])
            @endforeach
        </ul>
        @endif
    </li>
    @endforeach
</ul>
