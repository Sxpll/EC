@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Manage Categories</h1>
            <a href="{{ route('categories.create') }}" class="btn btn-success mb-3 btn-success1">Add New Category</a>

            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            <!-- Drzewo kategorii z Nestable -->
            <div class="dd" id="category-tree">
                <ol class="dd-list">
                    @foreach ($categories as $category)
                    <li class="dd-item" data-id="{{ $category->id }}">
                        <div class="dd-handle">{{ $category->name }}</div>
                        @if ($category->childrenRecursive->count())
                        <ol class="dd-list">
                            @foreach ($category->childrenRecursive as $child)
                            @include('categories.category-tree', ['categories' => $category->childrenRecursive, 'selectedCategories' => $selectedCategories ?? []])
                            @endforeach
                        </ol>
                        @endif
                    </li>
                    @endforeach
                </ol>
            </div>

            <!-- Przycisk do zapisywania zmian -->
            <button id="saveHierarchy" class="btn btn-primary mt-3">Save Hierarchy</button>
        </div>
    </div>

    <!-- Przycisk Back poniżej tabeli -->
    <div class="text-center mt-4">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-back">Back</a>
    </div>
</div>
@endsection

@section('scripts')
<!-- Dodanie skryptu jQuery i Nestable -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/nestable2@1.6.0/dist/jquery.nestable.min.js"></script>

<script>
    $(document).ready(function() {
        $('#category-tree').nestable({
            group: 1
        });

        $('#saveHierarchy').on('click', function() {
            var hierarchy = $('#category-tree').nestable('serialize');

            $.ajax({
                url: '{{ route("categories.updateHierarchy") }}',
                method: 'POST',
                data: {
                    hierarchy: hierarchy,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    alert('Hierarchy saved successfully');
                },
                error: function(error) {
                    console.error('Error saving hierarchy:', error);
                    alert('Failed to save hierarchy');
                }
            });
        });
    });
</script>
@endsection
