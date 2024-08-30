<li id="category_{{ $category->id }}">{{ $category->name }}
    @if ($category->childrenRecursive->count())
    <ul>
        @foreach ($category->childrenRecursive as $child)
        @include('categories.category-node', ['category' => $child])
        @endforeach
    </ul>
    @endif
</li>
