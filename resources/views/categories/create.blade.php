@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add New Category</h1>
    <form action="{{ route('categories.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="parent_id">Parent Category</label>
            <select name="parent_id" id="parent_id" class="form-control">
                <option value="">No Parent</option>
                @foreach($categories as $category)
                @include('categories.category-option', ['category' => $category, 'level' => 0])
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success">Add Category</button>
        <div class="row mt-3">
            <div class="col text-center">
                <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-back">Back</a>
            </div>
        </div>
    </form>
</div>
@endsection
