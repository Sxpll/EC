@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <h1>Edit Category</h1>
            <form action="{{ route('categories.update', $category->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Category Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ $category->name }}" required>
                </div>

                <div class="form-group">
                    <label for="parent_id">Parent Category</label>
                    <div id="category-tree">
                        @include('categories.category-tree', ['categories' => $categories, 'selected' => $category->parent_id])
                    </div>
                </div>

                <input type="hidden" name="parent_id" id="selected-parent-id" value="{{ $category->parent_id }}">
                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>

                <div class="form-group text-center">
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-back" style="display: inline-block; width: auto;">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('#category-tree input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('selected-parent-id').value = this.value;
        });
    });
</script>
@endsection
