@extends('layouts.app')

@section('content')
<div class="container-admin-dashboard">
    <div class="card-admin-dashboard">
        <div class="card-header">
            <h1>Admin Dashboard</h1>
            <p>Welcome, Admin!</p>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Informacje o użytkownikach -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h3>Total Users</h3>
                            <p>{{ $totalUsers }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h3>Active Users</h3>
                            <p>{{ $activeUsers }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h3>Inactive Users</h3>
                            <p>{{ $inactiveUsers }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h3>Deleted Users</h3>
                            <p>{{ $deletedUsers }}</p>
                        </div>
                    </div>
                </div>

                <!-- Informacje o produktach -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h3>Total Products</h3>
                            <p>{{ $totalProducts }}</p>
                        </div>
                    </div>
                </div>

                <!-- Informacje o kategoriach -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h3>Total Categories</h3>
                            <p>{{ \App\Models\Category::count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4 justify-content-center">
                <div class="col-md-6 text-center">
                    <a href="{{ route('admin.manageUsers') }}" class="btn btn-primary btn-block dashboard-btn">Manage Users</a>
                    <a href="{{ route('products.index') }}" class="btn btn-primary btn-block dashboard-btn">Manage Products</a>
                    <a href="{{ route('categories.index') }}" class="btn btn-primary btn-block dashboard-btn">Manage Categories</a>
                    <a href="{{ route('admin.orders') }}" class="btn btn-primary btn-block dashboard-btn">Manage Orders</a>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
