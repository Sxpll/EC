@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Your Cart</h1>
    @if(session('cart') && count(session('cart')) > 0)
    <table class="cart-table table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Image</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach(session('cart') as $id => $item)
            @php $subtotal = $item['price'] * $item['quantity']; @endphp
            @php $total += $subtotal; @endphp
            <tr data-id="{{ $id }}">
                <td>{{ $item['name'] }}</td>
                <td><img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" class="product-image"></td>

                <td>
                    <div class="quantity-control">
                        <button class="btn-quantity decrease-quantity" data-id="{{ $id }}">-</button>
                        <span class="quantity">{{ $item['quantity'] }}</span>
                        <button class="btn-quantity increase-quantity" data-id="{{ $id }}">+</button>
                    </div>
                </td>
                <td>{{ number_format($item['price'], 2) }} zł</td>
                <td class="subtotal">{{ number_format($subtotal, 2) }} zł</td>
                <td>
                    <form action="{{ route('cart.remove', $id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger">Remove</button>
                    </form>
                </td>
            </tr>
            @endforeach
            <tr>
                <td colspan="4"><strong>Total</strong></td>
                <td colspan="2" id="total">{{ number_format($total, 2) }} zł</td>
            </tr>
        </tbody>
    </table>

    <div class="cart-actions">
        <a href="{{ route('orders.create') }}" class="btn btn-success">Place Order</a>
        <form action="{{ route('cart.clear') }}" method="POST" style="display: inline-block;">
            @csrf
            <button type="submit" class="btn btn-warning">Clear Cart</button>
        </form>
    </div>
    @else
    <p>Your cart is empty.</p>
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
                        document.querySelector(`tr[data-id="${id}"] .quantity`).innerText = data.item.quantity;
                        document.querySelector(`tr[data-id="${id}"] .subtotal`).innerText = data.item.subtotal.toFixed(2) + ' zł';
                        document.getElementById('total').innerText = data.total.toFixed(2) + ' zł';
                    }
                });
        };

        document.querySelectorAll('.increase-quantity').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                let quantity = parseInt(button.previousElementSibling.innerText);
                updateCart(id, quantity + 1);
            });
        });

        document.querySelectorAll('.decrease-quantity').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                let quantity = parseInt(button.nextElementSibling.innerText);
                if (quantity > 1) {
                    updateCart(id, quantity - 1);
                }
            });
        });
    });
</script>
@endsection
