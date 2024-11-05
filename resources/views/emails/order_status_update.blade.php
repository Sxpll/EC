<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktualizacja Statusu Zamówienia</title>
</head>

<body>
    <h1>Aktualizacja statusu Twojego zamówienia #{{ $order->id }}</h1>
    <p>Twoje zamówienie jest teraz w statusie: <strong>{{ $statusName }}</strong></p>

    <p>Możesz sprawdzić szczegóły zamówienia tutaj:</p>
    <a href="{{ route('orders.myOrders') }}">Panel zarządzania zamówieniami</a>

    <p>Dziękujemy za zakupy w naszym sklepie!</p>
</body>

</html>
