@extends('layouts.app')

@section('content')
<div class="main-container">
    <!-- Sidebar z kategoriami - widoczny na większych ekranach -->
    <div class="custom-sidebar d-none d-md-block">
        <div class="floating-sidebar shadow">
            <h3 class="sidebar-header">Kategorie</h3>
            <div class="category-wrapper">
                <ul class="category-tree">
                    @foreach ($categories as $category)
                    @include('partials.category-node', ['category' => $category])
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Główna sekcja z produktami -->
    <div class="main-content">
        <!-- Wyszukiwarka i sortowanie na większych ekranach -->
        <div class="filter-sort sort-large d-flex justify-content-between align-items-center flex-wrap">
            <div class="search-container flex-grow-1 mb-2">
                <form action="{{ route('products.publicIndex') }}" method="GET" class="search-form d-flex align-items-center">
                    <input type="text" name="search" class="search-input" placeholder="Wyszukaj produkty..." value="{{ request()->input('search') }}">
                    <button class="btn btn-primary ml-2">Szukaj</button>
                </form>
            </div>
            <div class="sort-container mb-2">
                <form action="{{ route('products.publicIndex') }}" method="GET">
                    <select class="filter-select" name="sort_by" onchange="this.form.submit()">
                        <option value="">Sortuj według</option>
                        <option value="name_asc" {{ request()->input('sort_by') == 'name_asc' ? 'selected' : '' }}>Nazwa (A-Z)</option>
                        <option value="name_desc" {{ request()->input('sort_by') == 'name_desc' ? 'selected' : '' }}>Nazwa (Z-A)</option>
                        <option value="price_asc" {{ request()->input('sort_by') == 'price_asc' ? 'selected' : '' }}>Cena (od najniższej)</option>
                        <option value="price_desc" {{ request()->input('sort_by') == 'price_desc' ? 'selected' : '' }}>Cena (od najwyższej)</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Przyciski Filtruj i Sortuj na mniejszych ekranach -->
        <div class="sticky-filter sort-small">
            <button id="openFilterModal" class="btn btn-primary filtrujprzycisk">Filtruj</button>
            <form action="{{ route('products.publicIndex') }}" method="GET" class="sort-container">
                <select class="filter-select" name="sort_by" onchange="this.form.submit()">
                    <option value="">Sortuj według</option>
                    <option value="name_asc">Nazwa (A-Z)</option>
                    <option value="name_desc">Nazwa (Z-A)</option>
                    <option value="price_asc">Cena (od najniższej)</option>
                    <option value="price_desc">Cena (od najwyższej)</option>
                </select>
            </form>
        </div>

        <!-- Grid produktów -->
        <div class="product-grid row" id="products-list">
            @foreach($products as $product)
            <div class="product-card">
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

<!-- Modal Filtrów -->
<div id="filterModal" class="modal-category">
    <div class="modal-category-content">
        <span id="closeFilterModal" class="close-category-modal">&times;</span>
        <h2>Kategorie</h2>
        <ul class="category-tree">
            @foreach ($categories as $category)
            <li class="category-item {{ request()->input('category_id') == $category->id ? 'selected-category' : '' }}">
                <a href="{{ route('products.publicIndex', ['category_id' => $category->id]) }}">
                    {{ $category->name }}
                </a>
                @if (request()->input('category_id') == $category->id)
                <span class="remove-category" data-category-id="{{ $category->id }}">X</span>
                @endif

                @if ($category->children->count())
                <ul class="subcategory-tree">
                    @foreach ($category->children as $child)
                    <li class="category-item {{ request()->input('category_id') == $child->id ? 'selected-category' : '' }}">
                        <a href="{{ route('products.publicIndex', ['category_id' => $child->id]) }}">
                            {{ $child->name }}
                        </a>
                        @if (request()->input('category_id') == $child->id)
                        <span class="remove-category" data-category-id="{{ $child->id }}">X</span>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const openFilterModalBtn = document.getElementById('openFilterModal');
        if (openFilterModalBtn) {
            openFilterModalBtn.addEventListener('click', function() {
                document.getElementById('filterModal').style.display = 'block';
            });
        }

        const closeFilterModalBtn = document.getElementById('closeFilterModal');
        if (closeFilterModalBtn) {
            closeFilterModalBtn.addEventListener('click', function() {
                document.getElementById('filterModal').style.display = 'none';
            });
        }

        document.querySelectorAll('.remove-category').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(window.location.href);
                url.searchParams.delete('category_id');
                window.location.href = url.toString();
            });
        });

        window.addEventListener('click', function(event) {
            const filterModal = document.getElementById('filterModal');
            if (event.target == filterModal) {
                filterModal.style.display = 'none';
            }
        });

        let lastScrollTop = 0;
        const navbar = document.querySelector('.navbar');
        const stickyFilter = document.querySelector('.sticky-filter');

        if (navbar && stickyFilter) {
            window.addEventListener('scroll', function() {
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                if (scrollTop > lastScrollTop) {
                    navbar.style.top = '-100px';
                    stickyFilter.style.top = '0';
                } else {
                    navbar.style.top = '0';
                    stickyFilter.style.top = '60px';
                }

                lastScrollTop = scrollTop;
            });
        }

        const handleCategoryTree = (selector) => {
            document.querySelectorAll(selector).forEach(function(categoryItem) {
                const subcategoryTree = categoryItem.querySelector('.subcategory-tree');
                const toggleArrow = document.createElement('span');
                toggleArrow.className = 'toggle-arrow';
                toggleArrow.innerHTML = subcategoryTree && !categoryItem.classList.contains('open') ? '&#9654;' : '&#9660;';

                categoryItem.prepend(toggleArrow);

                toggleArrow.addEventListener('click', function() {
                    categoryItem.classList.toggle('open');
                    toggleArrow.innerHTML = categoryItem.classList.contains('open') ? '&#9660;' : '&#9654;';
                    if (subcategoryTree) {
                        subcategoryTree.classList.toggle('open');
                    }
                });

                if (categoryItem.classList.contains('selected-category')) {
                    categoryItem.classList.add('open');
                    if (subcategoryTree) {
                        subcategoryTree.classList.add('open');
                        toggleArrow.innerHTML = '&#9660;';
                    }
                    if (!categoryItem.querySelector('.remove-category')) {
                        const removeBtn = document.createElement('span');
                        removeBtn.className = 'remove-category';
                        removeBtn.textContent = 'X';
                        removeBtn.style.marginLeft = '10px';
                        categoryItem.querySelector('a').after(removeBtn);

                        removeBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            const url = new URL(window.location.href);
                            url.searchParams.delete('category_id');
                            window.location.href = url.toString();
                        });
                    }
                }
            });
        };

        handleCategoryTree('.custom-sidebar .category-item');
        handleCategoryTree('#filterModal .category-item');

        let page = 2;
        const showMoreBtn = document.getElementById('show-more-btn');
        if (showMoreBtn) {
            showMoreBtn.addEventListener('click', function() {
                let url = `{{ route('products.publicIndex') }}?page=${page}`;
                let categoryId = "{{ request()->input('category_id') }}";
                if (categoryId) {
                    url += `&category_id=${categoryId}`;
                }
                let sortBy = "{{ request()->input('sort_by') }}";
                if (sortBy) {
                    url += `&sort_by=${sortBy}`;
                }
                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const productsList = document.getElementById('products-list');
                        productsList.innerHTML += data.html;

                        page++;
                        if (!data.hasMore) {
                            showMoreBtn.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                    });
            });
        }
    });
</script>
@endsection
