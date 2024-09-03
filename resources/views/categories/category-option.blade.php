@php
$level = $level ?? 0;
$prefix = str_repeat('⎯ ', $level); // Dodajemy znaki graficzne dla lepszej czytelności
@endphp

<option value="{{ $category->id }}">
    {{ $prefix }}{{ $category->name }}
</option>
@if ($category->childrenRecursive->isNotEmpty())
@foreach ($category->childrenRecursive as $child)
@include('categories.category-option', ['category' => $child, 'level' => $level + 1])
@endforeach
@endif
