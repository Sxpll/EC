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
                <div class="dashboard-card">
                    <h3>Total Users</h3>
                    <p>{{ $totalUsers }}</p>
                </div>
                <div class="dashboard-card">
                    <h3>Active Users</h3>
                    <p>{{ $activeUsers }}</p>
                </div>
                <div class="dashboard-card">
                    <h3>Inactive Users</h3>
                    <p>{{ $inactiveUsers }}</p>
                </div>
                <div class="dashboard-card">
                    <h3>Deleted Users</h3>
                    <p>{{ $deletedUsers }}</p>
                </div>

                <!-- Informacje o produktach -->
                <div class="dashboard-card">
                    <h3>Total Products</h3>
                    <p>{{ $totalProducts }}</p>
                </div>

                <!-- Informacje o kategoriach -->
                <div class="dashboard-card">
                    <h3>Total Categories</h3>
                    <p>{{ \App\Models\Category::count() }}</p>
                </div>
            </div>

            <div class="dashboard-actions">
                <a href="{{ route('admin.manageUsers') }}" class="btn dashboard-btn">Manage Users</a>
                <a href="{{ route('products.index') }}" class="btn dashboard-btn">Manage Products</a>
                <a href="{{ route('categories.index') }}" class="btn dashboard-btn">Manage Categories</a>
                <a href="{{ route('admin.orders') }}" class="btn dashboard-btn">Manage Orders</a>
            </div>
        </div>
    </div>
</div>
@endsection
