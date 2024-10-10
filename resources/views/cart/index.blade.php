@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Your Cart</h1>
    @if(session('cart') && count(session('cart')) > 0)
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
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
            <tr>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['quantity'] }}</td>
                <td>{{ number_format($item['price'], 2) }} zł</td>
                <td>{{ number_format($subtotal, 2) }} zł</td>
                <td>
                    <!-- Actions like update quantity or remove item -->
                </td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td colspan="2">{{ number_format($total, 2) }} zł</td>
            </tr>
        </tbody>
    </table>

    <div class="text-right">
        <a href="{{ route('orders.create') }}" class="btn btn-success">Place Order</a>
    </div>
    @else
    <p>Your cart is empty.</p>
    @endif
</div>
@endsection
