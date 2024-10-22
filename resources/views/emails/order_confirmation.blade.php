<!DOCTYPE html>
<html>

<head>
    <title>Potwierdzenie zamówienia</title>
</head>

<body>
    <h1>Dziękujemy za złożenie zamówienia!</h1>
    <p>Twoje zamówienie numer #{{ $order->id }} zostało pomyślnie złożone.</p>

    <h2>Produkty:</h2>
    <ul>
        @foreach($order->orderItems as $item)
        <li>
            <a href="{{ route('products.show', $item->product->id) }}">{{ $item->product->name }}</a> x {{ $item->quantity }}
        </li>
        @endforeach
    </ul>

    <p><strong>Łączna kwota:</strong> {{ number_format($order->total, 2) }} zł</p>

    <p>Możesz zarządzać swoim zamówieniem pod tym linkiem:</p>
    <p><a href="{{ route('orders.myOrders') }}">Panel zarządzania zamówieniami</a></p>

    <p>Dziękujemy za zakupy w naszym sklepie!</p>
</body>

</html>
