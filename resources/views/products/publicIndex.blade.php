@extends('layouts.app')

@section('content')
<div class="main-container">
    <!-- Sidebar z kategoriami -->
    <div class="custom-sidebar">
        <div class="floating-sidebar shadow">
            <h3>Kategorie</h3>
            <ul class="category-tree">
                @foreach ($categories as $category)
                @include('partials.category-node', ['category' => $category])
                @endforeach
            </ul>
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
            <!-- Sortowanie -->
            <form action="{{ route('products.publicIndex') }}" method="GET" class="d-flex">
                <select class="filter-select" name="sort_by" onchange="this.form.submit()">
                    <option value="">Sortuj według</option>
                    <option value="name_asc" {{ request()->input('sort_by') == 'name_asc' ? 'selected' : '' }}>Nazwa (A-Z)</option>
                    <option value="name_desc" {{ request()->input('sort_by') == 'name_desc' ? 'selected' : '' }}>Nazwa (Z-A)</option>
                    <option value="price_asc" {{ request()->input('sort_by') == 'price_asc' ? 'selected' : '' }}>Cena (od najniższej)</option>
                    <option value="price_desc" {{ request()->input('sort_by') == 'price_desc' ? 'selected' : '' }}>Cena (od najwyższej)</option>
                </select>
            </form>
        </div>

        <!-- Grid produktów -->
        <div class="product-grid row" id="products-list">
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
                        <p class="card-text"><strong>Cena:</strong> {{ number_format($product->price, 2) }} zł</p>
                        <p class="card-text"><strong>Dostępność:</strong>
                            @if ($product->availability === 'available')
                            Dostępny
                            @elseif ($product->availability === 'available_in_7_days')
                            Dostępny w ciągu 7 dni
                            @elseif ($product->availability === 'available_in_14_days')
                            Dostępny w ciągu 14 dni
                            @else
                            Niedostępny
                            @endif
                        </p>
                    </div>
                    <div class="card-footer text-center">
                        <i class="fas fa-shopping-cart"></i>
                        <button class="btn btn-primary">Do koszyka</button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pokaż więcej - Tylko jeśli są jeszcze produkty do wyświetlenia -->
        @if($hasMorePages)
        <div class="pagination-wrapper">
            <button id="show-more-btn" class="btn btn-primary">Pokaż więcej</button>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    let page = 2;

    document.getElementById('show-more-btn').addEventListener('click', function() {
        // Konstruowanie dynamicznego URL z kategorią i sortowaniem
        let url = `/products00?page=${page}`;

        // Sprawdzamy, czy istnieje wartość dla category_id
        let categoryId = "{{ request()->input('category_id') }}";
        if (categoryId) {
            url += `&category_id=${categoryId}`;
        }

        // Sprawdzamy, czy istnieje wartość dla sort_by
        let sortBy = "{{ request()->input('sort_by') }}";
        if (sortBy) {
            url += `&sort_by=${sortBy}`;
        }

        // Wysyłanie zapytania AJAX
        fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // Informuje serwer, że to zapytanie AJAX
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Błąd sieci');
                }
                return response.json(); // Oczekuj odpowiedzi JSON
            })
            .then(data => {
                // Sprawdzenie, czy zwrócone dane zawierają html i hasMore
                if (!data.html || typeof data.hasMore === 'undefined') {
                    throw new Error('Błąd: Nieprawidłowa struktura danych z serwera');
                }

                // Dodawanie nowych produktów
                document.getElementById('products-list').innerHTML += data.html;
                page++; // Zwiększ numer strony

                // Ukryj przycisk, jeśli nie ma więcej produktów
                if (!data.hasMore) {
                    document.getElementById('show-more-btn').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Błąd podczas przetwarzania:', error); // Obsłuż błąd
            });
    });
</script>
@endsection
