<!DOCTYPE html>
<html>

<head>
    <title>Zamówienie w drodze - Kod odbioru</title>
</head>

<body>
    <h1>Twoje zamówienie #{{ $order->id }} jest w drodze!</h1>
    <p>Aby odebrać zamówienie, użyj poniższego kodu:</p>

    <h2>Kod odbioru: {{ $order->pickup_code }}</h2>

    <p>Możesz sprawdzić szczegóły zamówienia tutaj:</p>
    <p><a href="{{ route('orders.myOrders') }}">Panel zarządzania zamówieniami</a></p>

    <p>Dziękujemy za zakupy w naszym sklepie!</p>
</body>

</html>
