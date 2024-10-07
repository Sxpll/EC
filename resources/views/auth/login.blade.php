@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="container-center">
        <div class="card login-card">
            <div class="card-header">
                <h1>NAZWA</h1>
                <p>Welcome Back!</p>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            {{ __('Remember Me') }}
                        </label>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Sign in') }}
                        </button>
                    </div>

                    @if (Route::has('password.request'))
                    <a class="btn btn-primary" href="{{ route('password.request') }}">
                        {{ __('Forgot Your Password?') }}
                    </a>
                    @endif

                    <a class="btn btn-primary" href="{{ route('register') }}">
                        {{ __('Request An Account') }}
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
