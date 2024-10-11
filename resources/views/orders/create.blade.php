@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Place Order</h1>
    <form action="{{ route('orders.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="customer_name">Imię i nazwisko:</label>
            <input type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="customer_email">Email:</label>
            <input type="email" name="customer_email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="customer_address">Adres:</label>
            <textarea name="customer_address" class="form-control" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Złóż zamówienie</button>
    </form>

</div>
@endsection
