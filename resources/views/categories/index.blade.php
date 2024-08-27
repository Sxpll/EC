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
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                    <tr class="{{ !$category->isActive ? 'table-danger' : '' }}">
                        <td>{{ $category->name }}</td>
                        <td>
                            @if($category->isActive)
                            <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-primary">Edit</a>
                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Deactivate</button>
                            </form>
                            @else
                            <form action="{{ route('categories.activate', $category->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success">Activate</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>

    <!-- Przycisk Back poniżej tabeli -->
    <div class="text-center mt-4">
        <a href="{{route('admin.dashboard') }}" class="btn btn-secondary btn-back">Back</a>
    </div>
</div>
@endsection
