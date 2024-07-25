@extends('layouts.app')

@section('content')
<div class="container-center">
    <div class="card login-card">
        <div class="card-header">
            <h1>Admin Dashboard</h1>
        </div>
        <div class="card-body">
            <p>Welcome, Admin!</p>
            <a href="{{ route('admin.manageUsers') }}" class="btn btn-primary">Manage Users</a>
        </div>
    </div>
</div>
@endsection
