@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Wybierz koszyk</h1>
    <p>Masz produkty w koszyku z poprzedniej sesji. Wybierz, który koszyk chcesz użyć:</p>

    <form action="{{ route('cart.useCookieCart') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">Użyj koszyka z tej sesji</button>
    </form>

    <form action="{{ route('cart.useDatabaseCart') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">Użyj koszyka z konta</button>
    </form>

    <form action="{{ route('cart.mergeCarts') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">Połącz koszyki</button>
    </form>
</div>
@endsection
