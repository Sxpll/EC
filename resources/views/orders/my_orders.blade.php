@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Moje Zamówienia</h1>

    @if($orders->isEmpty())
    <p>Nie masz zamówień.</p>
    @else
    <div class="orders-table-wrapper">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>ID Zamówienia</th>
                    <th>Data</th>
                    <th>Łączna Kwota</th>
                    <th>Status</th>
                    <th>Szczegóły</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr class="order-row">
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                    <td>{{ number_format($order->total, 2) }} zł</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>
                        <button class="btn-details" onclick="toggleDetails({{ $order->id }})">
                            Pokaż Szczegóły <i class="expand-icon" id="expand-icon-{{ $order->id }}"></i>
                        </button>
                    </td>
                </tr>
                <tr id="order-details-{{ $order->id }}" class="orders-details" style="display: none;">
                    <td colspan="5">
                        <h5>Produkty w Zamówieniu:</h5>
                        <ul>
                            @foreach($order->orderItems as $item)
                            <li>
                                {{ $item->product->name }} x {{ $item->quantity }} - {{ number_format($item->price * $item->quantity, 2) }} zł
                            </li>
                            @endforeach
                        </ul>

                        <!-- Sekcja z kodem rabatowym, jeśli zastosowano -->
                        @if($order->discountCode)
                        <p><strong>Kod Rabatowy:</strong> {{ $order->discountCode->description }}</p>
                        <p><strong>Wartość Rabatu:</strong> -{{ number_format($order->discount_amount, 2) }} zł</p>
                        @endif

                        <p><strong>Łączna Kwota po Rabacie:</strong> {{ number_format($order->total, 2) }} zł</p>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<script>
    function toggleDetails(orderId) {
        const detailsRow = document.getElementById(`order-details-${orderId}`);
        const expandIcon = document.getElementById(`expand-icon-${orderId}`);
        detailsRow.style.display = detailsRow.style.display === 'none' ? 'table-row' : 'none';
        expandIcon.classList.toggle('rotated');
    }
</script>
@endsection
