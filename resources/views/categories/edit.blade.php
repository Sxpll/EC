@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Category</h1>
    <form id="categoryForm" action="{{ route('categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $category->name }}" required>
        </div>

        <div class="form-group">
            <label for="parent_id">Parent Category</label>
            <select name="parent_id" id="parent_id" class="form-control">
                <option value="">No Parent</option>
                @foreach($categories as $parentCategory)
                <option value="{{ $parentCategory->id }}" {{ $category->parent_id == $parentCategory->id ? 'selected' : '' }}>
                    {{ $parentCategory->name }}
                </option>
                @endforeach
            </select>
        </div>

        <!-- Przycisk do zapisywania zmian -->
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        console.log("Edit category loaded.");
    });
</script>
@endsection
