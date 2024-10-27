<li>
    <span class="caret">{{ $category->name }}</span>
    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
        {{ isset($selectedCategories) && in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
    <label>{{ $category->name }}</label>

    @if ($category->childrenRecursive->isNotEmpty())
    <ul class="nested">
        @foreach ($category->childrenRecursive as $childCategory)
        @include('partials.category-node-checkbox-item', ['category' => $childCategory, 'selectedCategories' => $selectedCategories ?? []])
        @endforeach
    </ul>
    @endif
</li>
