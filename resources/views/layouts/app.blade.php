<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
            <!-- resources/views/layouts/app.blade.php -->

            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo" style="height: 40px;">
                </a>
            </div>
        </nav>

        <!-- Sidebar -->
        <div id="mySidebar" class="sidebar">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
            @guest
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
            @else
                <a href="{{ url('/home') }}">Home</a>
                <a href="{{ route('account.edit') }}">My Account</a>
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
                @endif
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                                 document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            @endguest
        </div>

        <button id="sidebarBtn" onclick="openNav()">&#9776;</button>

        <main class="content-wrapper">
            @yield('content')
        </main>
    </div>

    <script>
        function openNav() {
            document.getElementById("mySidebar").style.width = "250px";
        }

        function closeNav() {
            document.getElementById("mySidebar").style.width = "0";
        }

        // Zamknij sidebar po kliknięciu w link
        document.querySelectorAll('#mySidebar a').forEach(link => {
            link.addEventListener('click', function() {
                closeNav();
            });
        });
    </script>
</body>
</html>
