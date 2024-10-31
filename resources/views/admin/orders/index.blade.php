@extends('layouts.app')

@section('content')
<div class="orders-container">
    <div class="orders-card">
        <div class="orders-card-header d-flex align-items-center">
            <a href="{{ route('admin.dashboard') }}" class="back-arrow mr-auto">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>Zarządzaj Zamówieniami</h1>
        </div>
        <div class="orders-card-body">
            @if(session('success'))
            <div class="alert alert-success text-center">
                {{ session('success') }}
            </div>
            @endif

            <div class="orders-table-wrapper">
                <table class="orders-table table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Nr Zamówienia</th>
                            <th>Użytkownik</th>
                            <th>Łącznie</th>
                            <th>Status</th>
                            <th>Data Zamówienia</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr class="order-row" onclick="toggleOrderDetails(this)">
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->user->name }} {{ $order->user->lastname }}</td>
                            <td>{{ number_format($order->total, 2) }} zł</td>
                            <td>{{ $order->status->name ?? 'Brak statusu' }}</td> <!-- Wyświetlanie nazwy statusu z relacji -->

                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <button class="btn-details btn btn-primary btn-sm">view</button>
                                <span class="expand-icon">&#9654;</span>
                            </td>
                        </tr>
                        <tr class="orders-details">
                            <td colspan="6">
                                <h5>Pozycje Zamówienia:</h5>
                                <ul>
                                    @foreach($order->orderItems as $item)
                                    <li>{{ $item->product->name }} x {{ $item->quantity }} - {{ number_format($item->price * $item->quantity, 2) }} zł</li>
                                    @endforeach
                                </ul>
                                <div class="order-management">
                                    <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" class="form-inline">
                                        @csrf
                                        <div class="form-group mb-2">
                                            <label for="status_id" class="mr-2">Status:</label>
                                            <select name="status_id" class="form-control">
                                                @foreach($statuses as $status)
                                                <option value="{{ $status->id }}" {{ $order->status_id == $status->id ? 'selected' : '' }}>
                                                    {{ $status->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <button type="submit" class="btn-update btn btn-success mb-2 ml-2">Zaktualizuj Status</button>
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
