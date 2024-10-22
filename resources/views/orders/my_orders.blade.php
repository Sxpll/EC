@extends('layouts.app')

@section('content')
<div class="container">
    <h1>My Orders</h1>

    @if($orders->isEmpty())
    <p>You have no orders.</p>
    @else
    <table class="orders-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr class="order-row">
                <td>{{ $order->id }}</td>
                <td>{{ $order->created_at }}</td>
                <td>{{ number_format($order->total, 2) }} zł</td>
                <td>{{ ucfirst($order->status) }}</td>
                <td>
                    <button class="btn-details" onclick="toggleDetails({{ $order->id }})">
                        View Details <i class="expand-icon" id="expand-icon-{{ $order->id }}"></i>
                    </button>
                </td>
            </tr>
            <tr id="order-details-{{ $order->id }}" class="orders-details">
                <td colspan="5">
                    <h5>Order Items:</h5>
                    <ul>
                        @foreach($order->orderItems as $item)
                        <li>{{ $item->product->name }} x {{ $item->quantity }} - {{ number_format($item->price * $item->quantity, 2) }} zł</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

<script>
    function toggleDetails(orderId) {
        const detailsRow = document.getElementById(`order-details-${orderId}`);
        const expandIcon = document.getElementById(`expand-icon-${orderId}`);
        detailsRow.classList.toggle('expanded');
        expandIcon.classList.toggle('rotated');
    }
</script>
@endsection
