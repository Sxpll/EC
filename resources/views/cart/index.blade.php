@extends('layouts.app')

@section('content')
<div class="custom-cart-container">
    <h1>Twój Koszyk</h1>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('cart') && count(session('cart')) > 0)
    <div class="custom-cart-actions-top">
        <form action="{{ route('cart.clear') }}" method="POST">
            @csrf
            <button type="submit" class="custom-btn-clear-cart">Wyczyść Koszyk</button>
        </form>
    </div>

    <div class="custom-cart-table-wrapper">
        <table class="custom-cart-table">
            <thead>
                <tr>
                    <th>Produkt</th>
                    <th>Zdjęcie</th>
                    <th>Ilość</th>
                    <th>Cena</th>
                    <th>Razem</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach(session('cart') as $id => $item)
                @php
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
                @endphp
                <tr data-id="{{ $id }}">
                    <td>{{ $item['name'] }}</td>
                    <td>
                        @if(!empty($item['image']))
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="custom-product-image">
                        @endif
                    </td>
                    <td>
                        <div class="custom-quantity-control">
                            <button type="button" class="custom-btn-decrease" data-id="{{ $id }}">-</button>
                            <input type="number" class="custom-quantity-input" data-id="{{ $id }}" value="{{ $item['quantity'] }}" min="1">
                            <button type="button" class="custom-btn-increase" data-id="{{ $id }}">+</button>
                        </div>
                    </td>
                    <td>{{ number_format($item['price'], 2) }} zł</td>
                    <td class="custom-subtotal">{{ number_format($subtotal, 2) }} zł</td>
                    <td>
                        <form action="{{ route('cart.remove', $id) }}" method="POST">
                            @csrf
                            <button type="submit" class="custom-btn-remove">Usuń</button>
                        </form>
                    </td>
                </tr>
                @endforeach

                @if(session('discount_amount'))
                <tr class="custom-discount-row">
                    <td colspan="4" class="custom-total-label"><strong>Rabat:</strong></td>
                    <td colspan="2" class="custom-total-amount"><strong>-{{ number_format(session('discount_amount'), 2) }} zł</strong></td>
                </tr>
                @endif

                <tr class="custom-total-row">
                    <td colspan="4" class="custom-total-label"><strong>Łącznie:</strong></td>
                    <td colspan="2" class="custom-total-amount">
                        <strong>
                            <span id="total-amount">{{ number_format($total - session('discount_amount', 0), 2) }}</span> zł
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="custom-discount-code-form">
        @if(session('discount_code'))
        <p>Zastosowano kod rabatowy: <strong>{{ session('discount_code') }}</strong></p>
        <form action="{{ route('cart.removeDiscount') }}" method="POST">
            @csrf
            <button type="submit" class="custom-btn-remove-discount">Usuń kod rabatowy</button>
        </form>
        @else
        <form action="{{ route('cart.applyDiscount') }}" method="POST">
            @csrf
            <label for="discount_code">Kod rabatowy:</label>
            <input type="text" name="discount_code" id="discount_code" required>
            <button type="submit" class="custom-btn-apply-discount">Zastosuj</button>
        </form>
        @endif
    </div>

    <div class="custom-cart-actions-bottom">
        <a href="{{ route('orders.create') }}" class="custom-btn-place-order">Złóż Zamówienie</a>
    </div>
    @else
    <p>Twój koszyk jest pusty.</p>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Zwiększ ilość
        document.querySelectorAll('.custom-btn-increase').forEach(button => {
            button.addEventListener('click', function() {
                updateQuantity(this.dataset.id, 1);
            });
        });

        // Zmniejsz ilość
        document.querySelectorAll('.custom-btn-decrease').forEach(button => {
            button.addEventListener('click', function() {
                updateQuantity(this.dataset.id, -1);
            });
        });

        // Aktualizacja ilości
        function updateQuantity(id, change) {
            let quantityInput = document.querySelector(`.custom-quantity-input[data-id='${id}']`);
            let newQuantity = parseInt(quantityInput.value) + change;
            if (newQuantity < 1) return;
            quantityInput.value = newQuantity;

            // Wysłanie żądania AJAX do zaktualizowania ilości na serwerze
            fetch(`/cart/update/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        quantity: newQuantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Zaktualizuj podsumowanie i łączną cenę
                        document.querySelector(`tr[data-id='${id}'] .custom-subtotal`).textContent = `${data.item.subtotal.toFixed(2)} zł`;
                        document.getElementById('total-amount').textContent = data.total_formatted;
                    }
                });
        }
    });
</script>
@endsection
