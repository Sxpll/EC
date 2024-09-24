@extends('layouts.app')

@section('content')
<div class="main-container">
    <!-- Sidebar z filtrami -->
    <div class="custom-sidebar">
        <div class="floating-sidebar shadow">
            <h3>Kategorie & Filtry</h3>
            <!-- Puste miejsce na przyszłe funkcje -->
        </div>
    </div>

    <!-- Główna sekcja z produktami -->
    <div class="main-content">
        <!-- Wyszukiwarka i sortowanie -->
        <div class="filter-sort d-flex justify-content-between align-items-center">
            <form action="{{ route('products.publicIndex') }}" method="GET" class="search-form d-flex">
                <input type="text" name="search" class="search-input" placeholder="Wyszukaj produkty..." value="{{ request()->input('search') }}">
                <button class="btn btn-primary ml-2">Szukaj</button>
            </form>
            <select class="filter-select" name="sort_by">
                <option value="">Sortuj według</option>
                <option value="price" {{ request()->input('sort_by') == 'price' ? 'selected' : '' }}>Cena</option>
                <option value="name" {{ request()->input('sort_by') == 'name' ? 'selected' : '' }}>Nazwa</option>
            </select>
        </div>

        <!-- Grid produktów -->
        <div class="product-grid row">
            @foreach($products as $product)
            <div class="product-card col-lg-3 col-md-4 col-sm-6 mb-4 d-flex align-items-stretch">
                <div class="card">
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
                        <button class="btn btn-primary">Do koszyka</button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Paginacja -->
        <div class="pagination-wrapper">
            {{ $products->links() }}
        </div>
        <div class="pagination-info">
            <p>Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results</p>
        </div>
    </div>
</div>
@endsection
