@extends('layouts.app')

@section('content')
<div class="product-page-container" style="display: flex; gap: 220px; margin-left: 50px;"> <!-- Używamy flexboxa do layoutu -->

    <!-- Lewa kolumna: Breadcrumb (ścieżka kategorii) oraz Galeria zdjęć -->
    <div class="left-column">
        <!-- Breadcrumb (ścieżka kategorii) -->
        <div class="breadcrumb" style="margin-bottom: 10px;">
            <a href="/">Home</a> /
            @if($product->categories->isNotEmpty())
            @php
            $category = $product->categories->first();
            $categoryTrail = [];

            // Rekurencyjne dodanie rodziców do ścieżki
            while ($category) {
            $categoryTrail[] = $category;
            $category = $category->parent;
            }

            // Odwracanie kolejności kategorii, aby zacząć od najwyższej
            $categoryTrail = array_reverse($categoryTrail);
            @endphp

            @foreach($categoryTrail as $trail)
            <a href="{{ route('categories.show', $trail->id) }}">{{ $trail->name }}</a> /
            @endforeach
            @endif
            {{ $product->name }}
        </div>

        <!-- Galeria zdjęć -->
        <div class="product-gallery" style="margin-top: 20px; position: relative; width: 400px; overflow: hidden;"> <!-- Dodane overflow: hidden -->

            <!-- Strzałki do przewijania -->
            <div class="arrow-left" style="position: absolute; top: 50%; left: 10px; transform: translateY(-50%); cursor: pointer; z-index: 2; color: #333; font-size: 24px;">&#10094;</div>
            <div class="arrow-right" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; z-index: 2; color: #333; font-size: 24px;">&#10095;</div>

            <!-- Główne zdjęcie -->
            <div class="main-image-wrapper" style="width: 100%; display: flex; transition: transform 0.5s ease;">
                @foreach($product->images as $image)
                <img class="mainImage" src="data:{{ $image->mime_type }};base64,{{ $image->file_data }}" alt="{{ $product->name }}" style="min-width: 100%; height: auto;">
                @endforeach
            </div>

            <!-- Miniaturki zdjęć -->
            <div class="thumbnails" style="margin-top: 10px; display: flex; gap: 10px;">
                @foreach($product->images as $image)
                <img src="data:{{ $image->mime_type }};base64,{{ $image->file_data }}" class="thumbnail-img" alt="{{ $product->name }}" style="width: 100px; height: 100px; object-fit: cover;">
                @endforeach
            </div>
        </div>
    </div>

    <!-- Prawa kolumna: Szczegóły produktu -->
    <div class="right-column">
        <!-- Nazwa produktu -->
        <h1>{{ $product->name }}</h1>

        <!-- Cena produktu -->
        <p><strong>Cena:</strong> {{ number_format($product->price, 2) }} zł</p>

        <!-- Gwiazdki -->
        <div class="product-rating" style="display: flex; align-items: center;">
            <span style="font-size: 24px; color: #f5c518;">&#9733;&#9733;&#9733;&#9733;&#9733;</span> <!-- Gwiazdki 5 na 5 -->
            <span style="font-size: 14px; color: #888; margin-left: 10px;">4.5/5</span> <!-- Ocena -->
        </div>

        <!-- Opis produktu -->
        <div class="product-description" style="margin-top: 20px;">
            <h3>Opis produktu:</h3>
            <p>{{ $product->description }}</p>
        </div>

        <!-- Przycisk do koszyka (nieaktywny) -->
        <button type="button" class="btn btn-primary" style="padding: 10px 20px; margin-top: 20px;">Dodaj do koszyka</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const images = Array.from(document.querySelectorAll('.mainImage')).map(img => img.src);
        const mainImageWrapper = document.querySelector('.main-image-wrapper');
        let currentIndex = 0;
        const arrowLeft = document.querySelector('.arrow-left');
        const arrowRight = document.querySelector('.arrow-right');
        const thumbnails = document.querySelectorAll('.thumbnail-img');

        // Funkcja do przesuwania obrazów
        function showImage(index) {
            currentIndex = (index + images.length) % images.length; // Zapętlenie indeksu
            const offset = -currentIndex * 100; // Obliczamy przesunięcie w procentach
            mainImageWrapper.style.transform = `translateX(${offset}%)`;
        }

        // Obsługa kliknięcia strzałki w lewo
        arrowLeft.addEventListener('click', function() {
            showImage(currentIndex - 1);
        });

        // Obsługa kliknięcia strzałki w prawo
        arrowRight.addEventListener('click', function() {
            showImage(currentIndex + 1);
        });

        // Obsługa kliknięcia miniaturki
        thumbnails.forEach((thumbnail, index) => {
            thumbnail.addEventListener('click', function() {
                showImage(index);
            });
        });
    });
</script>

<style>
    /* Styl dla Breadcrumb */
    .breadcrumb {
        font-size: 14px;
        color: #6c757d;
        text-align: left;
    }

    .breadcrumb a {
        color: #3498db;
        text-decoration: none;
    }

    .breadcrumb a:hover {
        text-decoration: underline;
    }

    /* Styl dla galerii */
    .main-image-wrapper {
        display: flex;
        transition: transform 0.5s ease;
    }

    .main-image-wrapper img {
        max-width: 100%;
        height: auto;
        object-fit: contain;
    }

    .thumbnails img {
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.3s ease;
    }

    .thumbnails img:hover {
        border-color: #3498db;
    }

    /* Strzałki do przewijania */
    .arrow-left,
    .arrow-right {
        font-size: 24px;
        color: #333;
        user-select: none;
        cursor: pointer;
        z-index: 2;
    }

    .arrow-left:hover,
    .arrow-right:hover {
        color: #3498db;
    }


    .right-column {
        margin-top: 80px;
    }

    /* Styl dla prawej kolumny */
    .right-column h1 {
        font-size: 28px;
        font-weight: bold;
    }

    .right-column p {
        font-size: 18px;
    }

    .product-rating {
        margin-top: 10px;
    }

    .product-description {
        margin-top: 20px;
        font-size: 16px;
    }

    .btn {
        background-color: #3498db;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }

    .btn:hover {
        background-color: #2980b9;
    }
</style>
@endsection
