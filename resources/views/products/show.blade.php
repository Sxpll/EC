@extends('layouts.app')

@section('content')
<div class="product-page-container">

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
        <div class="product-gallery" style="margin-top: 10px; position: relative; width: 400px; overflow: hidden;">

            <!-- Strzałki do przewijania -->
            <div class="arrow-left" style="position: absolute; top: 50%; left: 10px; transform: translateY(-50%); cursor: pointer; z-index: 2; color: #333; font-size: 24px;">&#10094;</div>
            <div class="arrow-right" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; z-index: 2; color: #333; font-size: 24px;">&#10095;</div>

            <!-- Główne zdjęcie -->
            <div class="main-image-wrapper" style="width: 100%; display: flex; transition: transform 0.5s ease;">
                @foreach($product->images as $image)
                <img class="mainImage" src="data:{{ $image->mime_type }};base64,{{ $image->file_data }}" alt="{{ $product->name }}" style="min-width: 100%; height: auto; cursor: pointer;">
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
            <span style="font-size: 24px; color: #f5c518;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            <span style="font-size: 14px; color: #888; margin-left: 10px;">4.5/5</span>
        </div>

        <!-- Opis produktu -->
        <div class="product-description" style="margin-top: 20px;">
            <h3>Opis produktu:</h3>
            <p>{{ $product->description }}</p>
        </div>

        <!-- Przycisk do koszyka -->
        <button type="button" class="btn btn-primary" style="padding: 10px 20px; margin-top: 20px;">Dodaj do koszyka</button>
    </div>
</div>

<!-- Modal dla powiększonego zdjęcia -->
<div id="imageModal" class="modal">
    <span class="close-modal">&times;</span>
    <div class="modal-arrow-left">&#10094;</div>
    <img class="modal-content" id="modalImage">
    <div class="modal-arrow-right">&#10095;</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const images = Array.from(document.querySelectorAll('.mainImage')).map(img => img.src);
        let currentIndex = 0;
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const closeModal = document.querySelector('.close-modal');
        const modalArrowLeft = document.querySelector('.modal-arrow-left');
        const modalArrowRight = document.querySelector('.modal-arrow-right');
        const arrowLeft = document.querySelector('.arrow-left');
        const arrowRight = document.querySelector('.arrow-right');
        const thumbnails = document.querySelectorAll('.thumbnail-img');
        const mainImageWrapper = document.querySelector('.main-image-wrapper');

        // Funkcja do przewijania zdjęć w galerii (poza modalem)
        function showGalleryImage(index) {
            currentIndex = (index + images.length) % images.length;
            const offset = -currentIndex * 100;
            mainImageWrapper.style.transform = `translateX(${offset}%)`;
        }

        // Funkcja do wyświetlania obrazu w modalu
        function showModalImage(index) {
            modal.style.display = "flex";
            modalImage.src = images[index];
            currentIndex = index;
        }

        // Obsługa kliknięcia w strzałki w galerii (poza modalem)
        arrowLeft.addEventListener('click', function() {
            showGalleryImage(currentIndex - 1);
        });

        arrowRight.addEventListener('click', function() {
            showGalleryImage(currentIndex + 1);
        });

        // Obsługa kliknięcia miniaturki (poza modalem)
        thumbnails.forEach((thumbnail, index) => {
            thumbnail.addEventListener('click', function() {
                showGalleryImage(index);
            });
        });

        // Kliknięcie w główne zdjęcie otwiera modal
        document.querySelectorAll('.mainImage').forEach((image, index) => {
            image.addEventListener('click', function() {
                showModalImage(index);
            });
        });

        // Zamknięcie modala po kliknięciu w X
        closeModal.addEventListener('click', function() {
            modal.style.display = "none";
        });

        // Zamknięcie modala po kliknięciu poza obrazkiem
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });

        // Obsługa kliknięcia strzałek w modalu
        modalArrowLeft.addEventListener('click', function() {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            modalImage.src = images[currentIndex];
        });

        modalArrowRight.addEventListener('click', function() {
            currentIndex = (currentIndex + 1) % images.length;
            modalImage.src = images[currentIndex];
        });
    });
</script>

<style>
    /* Styl dla Breadcrumb */
    .breadcrumb {
        font-size: 14px;
        color: gray;
        text-decoration: none;
        text-align: left;
        margin-top: 10px;
    }

    .breadcrumb a {
        color: gray;
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

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.9);
    }

    .modal-content {
        margin: auto;
        display: block;
        max-width: 25%;
        height: auto;
    }

    .close-modal {
        position: absolute;
        top: 15px;
        right: 35px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }

    .close-modal:hover,
    .close-modal:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
    }

    /* Modal strzałki */
    .modal-arrow-left,
    .modal-arrow-right {
        position: absolute;
        top: 50%;
        color: white;
        font-size: 40px;
        cursor: pointer;
        user-select: none;
        transform: translateY(-50%);
    }

    .modal-arrow-left {
        left: 10px;
    }

    .modal-arrow-right {
        right: 10px;
    }

    .modal-arrow-left:hover,
    .modal-arrow-right:hover {
        color: #bbb;
    }

    .product-page-container {
        display: flex;
        gap: 220px;
        margin-left: 50px;
        margin-top: 20px;
        margin-bottom: 20px;
        min-height: calc(100vh - 160px);
        justify-content: space-between;
        box-sizing: border-box;
    }
</style>
@endsection
