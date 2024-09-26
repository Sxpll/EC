<li>
    <a href="{{ route('products.publicIndex', ['category_id' => $category->id]) }}">{{ $category->name }}</a>
    @if ($category->childrenRecursive->isNotEmpty())
    <ul>
        @foreach ($category->childrenRecursive as $childCategory)
        @include('partials.category-node', ['category' => $childCategory])
        @endforeach
    </ul>
    @endif
</li>
