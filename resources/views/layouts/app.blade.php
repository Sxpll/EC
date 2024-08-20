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


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* Style specific to app.blade.php */
        .table td {
            white-space: normal;
            word-break: break-word;
        }

        .modal-content {
            width: 80%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .table-responsive {
            max-height: 60vh;
            overflow-y: auto;
        }

        body.modal-open {
            overflow: hidden;
        }

        .history-value {
            white-space: pre-wrap;
            word-break: break-word;
        }

        .history-table td {
            vertical-align: top;
        }

        .history-table .new-value-column {
            width: 300px;
        }

        /* Style for notification bell and navbar */
        .navbar-brand {
            display: flex;
            align-items: center;
        }

        .notification-bell-container {
            display: flex;
            align-items: center;
            margin-left: 30px;
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
            font-size: 24px;
            color: #007bff;
            margin-top: -13px;
        }

        .notification-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: rgb(121, 30, 30);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }

        /* Styl dla dynamicznego banera powiadomień */
        .notification-banner {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 16px;
            opacity: 0;
            transition: opacity 0.5s ease, transform 0.5s ease;
            z-index: 2000;
            backdrop-filter: blur(10px); /* Dodanie rozmytego tła */
        }

        .notification-banner.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .notification-banner.hide {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }
    </style>
</head>
<body>

<div id="app">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('img/logo.png') }}" alt="Logo" style="height: 40px;">
            </a>

            @if(auth()->check() && auth()->user()->role === 'admin')
            <div class="notification-bell-container">
                <div id="notificationBell" class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <span id="notificationCount" class="notification-count"></span>
                </div>
            </div>
            @endif
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

            @if (auth()->check() && auth()->user()->role == 'admin' && auth()->user()->is_hr)
                <a href="{{ route('chat.index') }}">Chat</a>
            @endif

            @if(auth()->check() && auth()->user()->role == 'user')
                <a href="{{ route('chat.userChats') }}">Chat</a>
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

    document.addEventListener('DOMContentLoaded', function() {
        const notificationBell = document.getElementById('notificationBell');
        const notificationsDropdown = document.getElementById('notificationsDropdown');

        if (notificationBell) {
            notificationBell.addEventListener('click', function () {
                console.log("Notification bell clicked");
                if (notificationsDropdown.style.display === 'block') {
                    notificationsDropdown.style.display = 'none';
                } else {
                    notificationsDropdown.style.display = 'block';
                }
            });
        }

        function showNotificationBanner(message) {
            const banner = document.createElement('div');
            banner.className = 'notification-banner';
            banner.innerText = message;
            document.body.appendChild(banner);
            setTimeout(() => banner.classList.add('show'), 100);
            setTimeout(() => {
                banner.classList.add('hide');
                banner.addEventListener('transitionend', () => banner.remove());
            }, 3000);
        }


    });

</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</body>
</html>
