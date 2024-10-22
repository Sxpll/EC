@extends('layouts.app')

@section('content')
<div class="custom-cart-container">
    <h1>Twój Koszyk</h1>
    @if(session('cart') && count(session('cart')) > 0)

    <!-- Przycisk "Wyczyść Koszyk" nad koszykiem -->
    <div class="custom-cart-actions-top">
        <form action="{{ route('cart.clear') }}" method="POST">
            @csrf
            <button type="submit" class="custom-btn-clear-cart">Wyczyść Koszyk</button>
        </form>
    </div>

    <!-- Kontener przewijalny dla tabeli koszyka -->
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
                            <button class="custom-btn-decrease" data-id="{{ $id }}">-</button>
                            <input type="number" class="custom-quantity-input" data-id="{{ $id }}" value="{{ $item['quantity'] }}" min="1">
                            <button class="custom-btn-increase" data-id="{{ $id }}">+</button>
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
                <tr class="custom-total-row">
                    <td colspan="4" class="custom-total-label"><strong>Łącznie:</strong></td>
                    <td colspan="2" class="custom-total-amount"><strong>{{ number_format($total, 2) }} zł</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Przyciski akcji poniżej koszyka -->
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
        const updateCart = (id, quantity) => {
            fetch(`/cart/update/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        quantity: quantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`tr[data-id="${id}"]`);
                        row.querySelector('.custom-quantity-input').value = data.item.quantity;
                        row.querySelector('.custom-subtotal').innerText = data.item.subtotal.toFixed(2) + ' zł';
                        document.querySelector('.custom-total-amount').innerText = data.total.toFixed(2) + ' zł';
                    }
                });
        };

        // Obsługa przycisków zwiększania ilości
        document.querySelectorAll('.custom-btn-increase').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                const quantityInput = document.querySelector(`.custom-quantity-input[data-id="${id}"]`);
                let quantity = parseInt(quantityInput.value) || 1;
                quantity += 1;
                quantityInput.value = quantity;
                updateCart(id, quantity);
            });
        });

        // Obsługa przycisków zmniejszania ilości
        document.querySelectorAll('.custom-btn-decrease').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                const quantityInput = document.querySelector(`.custom-quantity-input[data-id="${id}"]`);
                let quantity = parseInt(quantityInput.value) || 1;
                if (quantity > 1) {
                    quantity -= 1;
                    quantityInput.value = quantity;
                    updateCart(id, quantity);
                }
            });
        });

        // Obsługa zmiany wartości w polu input
        document.querySelectorAll('.custom-quantity-input').forEach(input => {
            input.addEventListener('change', () => {
                const id = input.dataset.id;
                let quantity = parseInt(input.value);
                if (quantity < 1 || isNaN(quantity)) {
                    quantity = 1;
                    input.value = 1;
                }
                updateCart(id, quantity);
            });
        });
    });
</script>
@endsection
