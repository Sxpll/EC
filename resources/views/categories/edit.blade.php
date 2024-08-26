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

                <!-- Przycisk Update Category -->
                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>

                <!-- Przycisk Back -->
                <div class="form-group text-center">
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-back" style="display: inline-block; width: auto;">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
