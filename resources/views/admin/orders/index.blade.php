@extends('layouts.app')

@section('content')
<div class="container-admin-dashboard">
    <div class="card-admin-dashboard">
        <div class="card-header">
            <a href="{{ route('admin.dashboard') }}" class="back-arrow" style="margin-right: auto;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>Manage Orders</h1>

        </div>
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Ordered At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr class="order-row" onclick="toggleOrderDetails(this)">
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->user->name }} {{ $order->user->lastname }}</td>
                        <td>{{ number_format($order->total, 2) }} zł</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>{{ $order->created_at }}</td>
                        <td>
                            <button class="btn-details">View</button>
                            <span class="expand-icon">&#9654;</span>
                        </td>
                    </tr>
                    <tr class="orders-details">
                        <td colspan="6">
                            <h5>Order Items:</h5>
                            <ul>
                                @foreach($order->orderItems as $item)
                                <li>{{ $item->product->name }} x {{ $item->quantity }} - {{ number_format($item->price * $item->quantity, 2) }} zł</li>
                                @endforeach
                            </ul>
                            <div class="order-management">
                                <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                                    @csrf
                                    <select name="status" class="form-control">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn-update">Update Status</button>
                                    <button type="button" class="btn-cancel">Cancel Order</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleOrderDetails(row) {
        const detailsRow = row.nextElementSibling;
        const icon = row.querySelector('.expand-icon');

        if (detailsRow.classList.contains('expanded')) {
            detailsRow.classList.remove('expanded');
            icon.classList.remove('rotated');
        } else {
            detailsRow.classList.add('expanded');
            icon.classList.add('rotated');
        }
    }
</script>
@endsection
