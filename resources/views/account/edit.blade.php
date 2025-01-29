@extends('layouts.app')

@section('content')
<div class="container-center">
    <div class="card login-card">
        <div class="card-header">
            <h1>Moje Dane</h1>
        </div>
        <div class="card-body">
            @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>


            @endif
            <form method="POST" action="{{ route('account.update') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Imię</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="lastname">Nazwisko</label>
                    <input id="lastname" type="text" class="form-control @error('lastname') is-invalid @enderror" name="lastname" value="{{ old('lastname', $user->lastname) }}" required>
                    @error('lastname')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Nowe Hasło (Pozostaw pustę, aby zachować obecnę hasło)</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password">
                    @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password-confirm">Potwierdź nowe hasło</label>
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation">
                </div>

                <div class="text-right mb-3">
                    <button type="submit" data-testid="update-account-button" class="btn btn-primary">
                        Zapisz
                    </button>
                </div>
            </form>

            <div class="text-right mb-3">
                <a href="{{ route('orders.myOrders') }}" class="btn btn-primary">Moje Zamówienia</a>
            </div>
            <div class="text-right mb-3">
                <a href="{{ route('discount_codes.my_codes') }}" class="btn btn-primary">Moje Kody Rabatowe</a>
            </div>


        </div>
    </div>
</div>
@endsection
