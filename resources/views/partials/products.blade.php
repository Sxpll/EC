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
