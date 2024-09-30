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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Ogólne style w ciemnym motywie */
        body.dark-mode {
            background-color: #121212;
            color: #e0e0e0;
        }

        /* Nagłówek i navbar w ciemnym motywie */
        body.dark-mode header {
            background-color: #1f1f1f;
            color: #e0e0e0;
        }

        body.dark-mode .navbar-links a {
            color: #e0e0e0;
        }

        body.dark-mode .navbar-icons a {
            color: #e0e0e0;
        }

        /* Karty w ciemnym motywie */
        body.dark-mode .card-admin,
        body.dark-mode .modal-content,
        body.dark-mode .card-admin-dashboard {
            background-color: #1f1f1f;
            color: #e0e0e0;
            border: 1px solid #333;
        }

        /* Przyciski w ciemnym motywie */
        body.dark-mode .btn {
            background-color: #333;
            color: #e0e0e0;
            border: 1px solid #444;
        }

        body.dark-mode .btn:hover {
            background-color: #555;
            color: #ffffff;
        }

        /* Formularze w ciemnym motywie */
        body.dark-mode input,
        body.dark-mode select,
        body.dark-mode textarea {
            background-color: #2b2b2b;
            color: #e0e0e0;
            border: 1px solid #444;
        }

        body.dark-mode input::placeholder,
        body.dark-mode textarea::placeholder {
            color: #888;
        }

        /* Ikony w navbarze */
        .navbar-icons {
            display: flex;
            gap: 15px;
            margin-left: auto;
            /* Przesuwa ikony na prawą stronę */
            padding-right: 40px;
            /* Dodaje odstęp od prawej krawędzi */
        }

        /* Styl dla ikon w ciemnym motywie */
        body.dark-mode .navbar-icons a {
            color: #e0e0e0;
            /* Ikony w ciemnym motywie */
        }

        body.dark-mode .navbar-icons a:hover {
            color: #ffffff;
            /* Jaśniejszy kolor po najechaniu */
        }
    </style>
</head>

<body>
    <div id="app">
        <!-- Navbar -->
        <header>
            <div class="navbar-container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo" style="height: 40px;">
                </a>
                <nav class="navbar-links">
                    <a href="{{ url('/home') }}">Home</a>
                    <a href="{{ route('products.publicIndex') }}">Products</a>
                    @if(Auth::check() && Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
                    @endif
                    <a href="{{ route('chat.index') }}">Chat</a>
                </nav>
                <div class="navbar-icons">
                    <div class="navbar-icons">
                        <div class="theme-switch">
                            <input type="checkbox" id="theme-toggle" class="theme-toggle-input">
                            <label for="theme-toggle" class="theme-toggle-label">
                                <span class="theme-icon theme-sun"><i class="fas fa-sun"></i></span>
                                <span class="theme-icon theme-moon"><i class="fas fa-moon"></i></span>
                            </label>
                        </div>
                        <!-- Zawsze widoczne ikony -->
                        <a href="{{ route('account.edit') }}"><i class="fa fa-user"></i></a>
                        <a><i class="fa fa-shopping-cart"></i></a>

                        <!-- Ikonka dzwonka tylko dla zalogowanego admina -->
                        @if(Auth::check() && Auth::user()->role === 'admin')
                        <a id="notificationBell" href="#"><i class="fa fa-bell"></i><span id="notificationCount" class="notification-count" style="display: none;"></span></a>
                        @endif

                        <!-- Ikonka logout tylko dla zalogowanych użytkowników -->
                        @if(Auth::check())
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-sign-out-alt"></i></a>
                        @endif
                    </div>
                </div>
        </header>

        <!-- Content -->
        <div class="welcome-container">
            <h1>Witaj</h1>
        </div>

        <!-- Footer -->
        <footer class="footer-bar">
            <div class="footer-bar-content">
                <a href="#">About Us</a>
                <a href="#">Privacy</a>
                <a href="#">FAQ</a>
                <a href="#">Careers</a>
            </div>
        </footer>
    </div>

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationBell = document.getElementById('notificationBell');
            const notificationsDropdown = document.getElementById('notificationsDropdown');

            if (notificationBell) {
                notificationBell.addEventListener('click', function() {
                    const isDisplayed = notificationsDropdown.style.display === 'block';
                    notificationsDropdown.style.display = isDisplayed ? 'none' : 'block';
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const toggleSwitch = document.getElementById('theme-toggle');
            const currentTheme = localStorage.getItem('theme') || 'light';

            if (currentTheme === 'dark') {
                document.body.classList.add('dark-mode');
                toggleSwitch.checked = true;
            }

            toggleSwitch.addEventListener('change', function() {
                if (toggleSwitch.checked) {
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('theme', 'light');
                }
            });
        });
    </script>
</body>

</html>
