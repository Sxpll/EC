<!DOCTYPE html>
<html>

<head>
    <title>Aktualizacja statusu zamówienia</title>
</head>

<body>
    <h1>Aktualizacja statusu Twojego zamówienia #{{ $order->id }}</h1>
    <p>Twój zamówienie jest teraz w statusie: <strong>{{ ucfirst($order->status) }}</strong></p>

    <p>Możesz sprawdzić szczegóły zamówienia tutaj:</p>
    <p><a href="{{ route('orders.myOrders') }}">Panel zarządzania zamówieniami</a></p>

    <p>Dziękujemy za zakupy w naszym sklepie!</p>
</body>

</html>
