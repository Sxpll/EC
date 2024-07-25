@extends('layouts.app')

@section('content')
<div class="container-center">
    <div class="card login-card">
        <div class="card-header">
            <h1>{{ __('Dashboard') }}</h1>
        </div>
        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            {{ __('You are logged in!') }}
        </div>
    </div>
</div>
@endsection
