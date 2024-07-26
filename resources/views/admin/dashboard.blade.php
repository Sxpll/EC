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
            </div>
            <div class="row mt-4 justify-content-center">
                <div class="col-md-6 text-center">
                    <a href="{{ route('admin.manageUsers') }}" class="btn btn-primary btn-block">Manage Users</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
