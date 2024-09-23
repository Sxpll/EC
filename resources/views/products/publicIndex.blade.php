@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Pływający prostokąt na filtry -->
    <div class="floating-sidebar shadow">
        <h3>Filters & Categories</h3>
        <!-- Tutaj przyszłe funkcje: wybór kategorii, filtry itp. -->
    </div>

    <!-- Główna sekcja z produktami -->
    <div class="products-section">
        <div class="row" id="product-grid">
            @foreach($products->where('isActive', true) as $product)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card h-100 shadow-sm rounded product-card">
                    @if($product->images->count())
                    <img src="data:{{ $product->images->first()->mime_type }};base64,{{ $product->images->first()->file_data }}" class="card-img-top" alt="{{ $product->name }}">
                    @else
                    <img src="https://via.placeholder.com/150" class="card-img-top" alt="{{ $product->name }}">
                    @endif
                    <div class="card-body d-flex flex-column justify-content-between">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="card-text">{{ Str::limit($product->description, 60) }}</p>
                    </div>
                    <div class="card-footer text-center">
                        <i class="fas fa-shopping-cart"></i> <!-- Ikona koszyka -->
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
