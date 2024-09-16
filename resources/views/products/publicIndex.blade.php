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
    <div class="container-products">
        @foreach($products->where('isActive', true) as $product)
        <div class="product-card">
            <div class="card shadow-sm rounded">
                @if($product->images->count())
                <img src="data:{{ $product->images->first()->mime_type }};base64,{{ $product->images->first()->file_data }}" class="card-img-top" alt="{{ $product->name }}">
                @else
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="{{ $product->name }}">
                @endif
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">{{ $product->name }}</h5>
                    <p class="card-text">{{ Str::limit($product->description, 100) }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Paginacja -->
    <div class="d-flex justify-content-center mt-4">
        {{ $products->links() }}
    </div>
</div>
@endsection
