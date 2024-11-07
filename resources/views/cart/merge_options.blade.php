@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Masz dwa koszyki</h2>
    <p>Wybierz, z którym koszykiem chcesz kontynuować:</p>

    <h3>Koszyk 1 (zalogowany użytkownik)</h3>
    @if (!empty($databaseCart))
    <ul>
        @foreach ($databaseCart as $item)
        <li>{{ $item['name'] }} - Ilość: {{ $item['quantity'] }} - Cena: {{ number_format($item['price'], 2) }} zł</li>
        @endforeach
    </ul>
    @else
    <p>Koszyk jest pusty.</p>
    @endif

    <h3>Koszyk 2 (przed zalogowaniem)</h3>
    @if (!empty($cookieCart))
    <ul>
        @foreach ($cookieCart as $item)
        <li>{{ $item['name'] }} - Ilość: {{ $item['quantity'] }} - Cena: {{ number_format($item['price'], 2) }} zł</li>
        @endforeach
    </ul>
    @else
    <p>Koszyk jest pusty.</p>
    @endif

    <form action="{{ route('cart.useSelectedCart') }}" method="POST">
        @csrf
        <p>Wybierz opcję:</p>
        <input type="radio" name="cart_option" value="database" id="database" required>
        <label for="database">Kontynuuj z koszykiem 1</label><br>

        <input type="radio" name="cart_option" value="cookie" id="cookie">
        <label for="cookie">Kontynuuj z koszykiem 2</label><br>

        <input type="radio" name="cart_option" value="merge" id="merge">
        <label for="merge">Połącz oba koszyki</label><br><br>

        <button type="submit" class="btn btn-primary">Kontynuuj</button>
    </form>
</div>
@endsection
