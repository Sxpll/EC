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
                <button type="submit" class="btn btn-primary">Update Category</button>
            </form>
            <!-- Przycisk Back -->
            <div class="text-center mt-4">
                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-back">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
