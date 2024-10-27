<div style="max-height: 300px; overflow-y: auto;">
    <ul>
        @foreach ($categories as $category)
        @include('partials.category-node-checkbox-item', ['category' => $category, 'selectedCategories' => $selectedCategories ?? []])
        @endforeach
    </ul>
</div>
