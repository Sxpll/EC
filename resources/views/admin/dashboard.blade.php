@extends('layouts.app')

@section('content')
<div class="container-admin-dashboard">
    <div class="card-admin-dashboard">
        <div class="card-header">
            <h1>Admin Dashboard</h1>
            <p>Welcome, Admin!</p>
        </div>
        <div class="card-body">
            <div class="dashboard-summary">
                <!-- Informacje o użytkownikach -->
                <!-- ... pozostałe karty ... -->
            </div>

            <div class="dashboard-actions">
                <a href="{{ route('admin.manageUsers') }}" class="btn dashboard-btn" data-testid="manage-users-link">Zarządzaj użytkownikami</a>
                <a href="{{ route('products.index') }}" class="btn dashboard-btn">Zarządzaj Produktami</a>
                <a href="{{ route('categories.index') }}" class="btn dashboard-btn">Zarządzaj Kategoriami</a>
                <a href="{{ route('admin.orders') }}" class="btn dashboard-btn">Zarządzaj Zamówieniami</a>
                <a href="{{ route('discount_codes.index') }}" class="btn dashboard-btn">Zarządzaj Kodami Rabatowymi</a>
            </div>
        </div>
    </div>
</div>
@endsection
