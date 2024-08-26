@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <h1>Add New Category</h1>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Category Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success1">Add Category</button>
            </form>



<!-- Przycisk Back umieszczony w nowym wierszu -->
<div class="row mt-3">
    <div class="col text-center">
    <a href="{{ route ('admin.dashboard') }}" class="btn btn-secondary btn-back">Back</a>
    </div>
</div>


        </div>
    </div>
</div>
@endsection
