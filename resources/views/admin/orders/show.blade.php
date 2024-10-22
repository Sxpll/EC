@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Order #{{ $order->id }} Details</h1>
    <p><strong>Customer Name:</strong> {{ $order->customer_name }}</p>
    <p><strong>Email:</strong> {{ $order->customer_email }}</p>
    <p><strong>Address:</strong> {{ $order->customer_address }}</p>
    <p><strong>Total:</strong> {{ number_format($order->total, 2) }} zł</p>

    <h3>Order Items:</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 2) }} zł</td>
                <td>{{ number_format($item->price * $item->quantity, 2) }} zł</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
