@extends('layouts.app')

@section('content')
<div class="container">
    <h1>My Orders</h1>

    @if($orders->isEmpty())
    <p>You have no orders.</p>
    @else
    @foreach($orders as $order)
    <div class="card mb-3">
        <div class="card-header">
            <strong>Order #{{ $order->id }}</strong>
            <span class="float-right">Date: {{ $order->created_at }}</span>
        </div>
        <div class="card-body">
            <p><strong>Total:</strong> {{ number_format($order->total, 2) }} zł</p>
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            <h5>Order Items:</h5>
            <ul>
                @foreach($order->orderItems as $item)
                <li>{{ $item->product->name }} x {{ $item->quantity }} - {{ number_format($item->price * $item->quantity, 2) }} zł</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endforeach
    @endif
</div>
@endsection
