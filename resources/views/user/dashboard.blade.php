@extends('layouts.app')

@section('content')
<div class="container-center">
    <div class="card login-card">
        <div class="card-header">
            <h1>User Dashboard</h1>
        </div>
        <div class="card-body">
            <p>Welcome, {{ Auth::user()->name }}!</p>
        </div>
    </div>
</div>
@endsection
