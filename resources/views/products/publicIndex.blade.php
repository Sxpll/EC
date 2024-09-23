@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar z filtrami -->
        <div class="col-lg-2 col-md-3">
            <div class="floating-sidebar shadow">
                <h3>Filters & Categories</h3>
                <!-- Tutaj przyszłe funkcje: wybór kategorii, filtry itp. -->
            </div>
        </div>

        <!-- Główna sekcja z produktami -->
        <div class="col-lg-10 col-md-9">
            <!-- Wyszukiwarka i sortowanie -->
            <div class="d-flex justify-content-between mb-4">
                <form action="{{ route('products.publicIndex') }}" method="GET" class="search-form d-flex">
                    <input type="text" name="search" class="form-control rounded-input" placeholder="Search products..." value="{{ request()->input('search') }}">
                    <button class="btn btn-primary ml-2">Search</button>
                </form>
                <select class="form-control rounded-input" id="sortProducts" name="sort_by">
                    <option value="">Sort by</option>
                    <option value="price">Price</option>
                    <option value="name">Name</option>
                </select>
            </div>

            <!-- Grid produktów -->
            <div class="row product-grid" id="product-grid">
                @foreach($products as $product)
                <div class="col-lg-2 col-md-3 col-sm-4 mb-4 d-flex align-items-stretch">
                    <div class="card product-card">
                        @if($product->images->count())
                        <img src="data:{{ $product->images->first()->mime_type }};base64,{{ $product->images->first()->file_data }}" class="card-img-top" alt="{{ $product->name }}">
                        @else
                        <img src="https://via.placeholder.com/150" class="card-img-top" alt="{{ $product->name }}">
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text">{{ Str::limit($product->description, 60) }}</p>
                        </div>
                        <div class="card-footer text-center">
                            <i class="fas fa-shopping-cart"></i>
                            <button class="btn btn-primary">Add to cart</button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
