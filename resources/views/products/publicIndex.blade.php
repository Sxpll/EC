@extends('layouts.product')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Products</h1>

    <!-- Wyszukiwarka produktów -->
    <form action="{{ route('products.publicIndex') }}" method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request()->input('search') }}">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
        </div>
    </form>

    <!-- Wyświetlanie produktów w siatce -->
    <div class="row">
        @foreach($products->where('isActive', true) as $product)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4 d-flex align-items-stretch">
            <div class="card h-100 shadow-sm rounded">
                @if($product->images->count())
                <img src="data:{{ $product->images->first()->mime_type }};base64,{{ $product->images->first()->file_data }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                @else
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ $product->name }}</h5>
                    <p class="card-text">{{ Str::limit($product->description, 100) }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Paginacja -->
    <div class="d-flex justify-content-center">
        {{ $products->links() }}
    </div>
</div>
@endsection
