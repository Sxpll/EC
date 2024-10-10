@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Orders Management</h1>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Placed At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->customer_name }} ({{ $order->customer_email }})</td>
                <td>{{ number_format($order->total, 2) }} zł</td>
                <td>{{ $order->created_at }}</td>
                <td>
                    <a href="{{ route('admin.orderDetails', $order->id) }}" class="btn btn-primary">Details</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
