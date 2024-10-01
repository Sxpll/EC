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
    <link href="{{ asset('css/responsive.css') }}" rel="stylesheet"> <!-- Dodane dla responsywności -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                    @if(auth()->check() && auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
                    @endif
                    <a href="{{ route('chat.index') }}">Chat</a>
                </nav>

                <div class="navbar-icons">
                    <!-- Theme Switch for logged-in users -->
                    @if(auth()->check())
                    <div class="theme-switch">
                        <input type="checkbox" id="theme-toggle-navbar" class="theme-toggle-input">
                        <label for="theme-toggle-navbar" class="theme-toggle-label">
                            <span class="theme-icon theme-sun"><i class="fas fa-sun"></i></span>
                            <span class="theme-icon theme-moon"><i class="fas fa-moon"></i></span>
                        </label>
                    </div>
                    @endif

                    @if(auth()->check())
                    <a href="{{ route('account.edit') }}" class="account-icon"><i class="fa fa-user"></i></a>
                    @endif
                    <a href="#"><i class="fa fa-shopping-cart"></i></a>
                    @if(auth()->check() && auth()->user()->role === 'admin')
                    <a href="#" id="notificationBell"><i class="fa fa-bell"></i><span id="notificationCount" class="notification-count" style="display: none;"></span></a>
                    @endif
                    @if(auth()->check())
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="logout-icon"><i class="fa fa-sign-out-alt"></i></a>
                    @endif
                    <button class="navbar-toggler" type="button" id="navbar-toggler">
                        <img src="{{ asset('img/hamburger-icon.png') }}" alt="Menu" style="height: 24px;">
                    </button>
                </div>
            </div>
        </header>

        <!-- Sidebar Menu -->
        <div id="sidebar" class="sidebar">
            <div class="theme-switch">
                <input type="checkbox" id="theme-toggle-sidebar" class="theme-toggle-input">
                <label for="theme-toggle-sidebar" class="theme-toggle-label">
                    <span class="theme-icon theme-sun"><i class="fas fa-sun"></i></span>
                    <span class="theme-icon theme-moon"><i class="fas fa-moon"></i></span>
                </label>
            </div>

            <button id="close-sidebar" class="close-sidebar">&times;</button>
            <nav class="sidebar-nav">
                <a href="{{ url('/home') }}">Home</a>
                <a href="{{ route('products.publicIndex') }}">Products</a>
                @if(auth()->check() && auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
                @endif
                <a href="{{ route('chat.index') }}">Chat</a>

                @if(auth()->check())
                <a href="{{ route('account.edit') }}">My Account</a>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                @endif
            </nav>
        </div>

        <!-- Notifications Dropdown -->
        <div id="notificationsDropdown" class="notifications-dropdown">
            <h6 class="dropdown-header">Notifications</h6>
            <div id="notificationList" class="notification-list"></div>
        </div>

        <main class="content-wrapper">
            @yield('content')
        </main>

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

    <!-- Scripts -->
    <script>
        // Theme switch logic
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggleNavbar = document.getElementById('theme-toggle-navbar');
            const themeToggleSidebar = document.getElementById('theme-toggle-sidebar');

            // Check saved theme in localStorage
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.classList.toggle('dark-mode', savedTheme === 'dark');
            themeToggleNavbar.checked = savedTheme === 'dark';
            themeToggleSidebar.checked = savedTheme === 'dark';

            // Toggle theme on switch change for navbar
            themeToggleNavbar.addEventListener('change', () => {
                const isDarkMode = themeToggleNavbar.checked;
                document.body.classList.toggle('dark-mode', isDarkMode);
                localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
                themeToggleSidebar.checked = isDarkMode; // Synchronize sidebar switch
            });

            // Toggle theme on switch change for sidebar
            themeToggleSidebar.addEventListener('change', () => {
                const isDarkMode = themeToggleSidebar.checked;
                document.body.classList.toggle('dark-mode', isDarkMode);
                localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
                themeToggleNavbar.checked = isDarkMode; // Synchronize navbar switch
            });
        });

        // Hamburger and sidebar functionality
        document.getElementById('navbar-toggler').addEventListener('click', function() {
            document.getElementById('sidebar').classList.add('open');
        });

        document.getElementById('close-sidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('open');
        });

        // Notifications functionality
        document.getElementById('notificationBell').addEventListener('click', function() {
            const notificationsDropdown = document.getElementById('notificationsDropdown');
            const isDisplayed = notificationsDropdown.style.display === 'block';
            notificationsDropdown.style.display = isDisplayed ? 'none' : 'block';
        });

        // Fetch notifications periodically
        function fetchNotifications() {
            const notificationList = document.getElementById('notificationList');
            const notificationCount = document.getElementById('notificationCount');

            if (!notificationList) return;

            axios.get('/notifications')
                .then(response => {
                    const notifications = response.data;
                    notificationList.innerHTML = '';

                    if (notifications.length > 0) {
                        notificationCount.style.display = 'inline-block';
                        notificationCount.innerText = notifications.length;
                        notifications.forEach(notification => {
                            const item = document.createElement('div');
                            item.classList.add('notification-item');
                            item.innerText = notification.message;
                            item.addEventListener('click', function() {
                                window.openChatWindow(notification.chat_id);
                            });
                            notificationList.appendChild(item);
                        });
                    } else {
                        notificationCount.style.display = 'none';
                        notificationList.innerHTML = '<div class="text-center">No new notifications</div>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching notifications:', error);
                });
        }

        fetchNotifications();
        setInterval(fetchNotifications, 5000);

        // Close notifications dropdown on outside click
        window.addEventListener('click', function(event) {
            const notificationsDropdown = document.getElementById('notificationsDropdown');
            const notificationBell = document.getElementById('notificationBell');
            if (notificationsDropdown && !notificationsDropdown.contains(event.target) && notificationBell && !notificationBell.contains(event.target)) {
                notificationsDropdown.style.display = 'none';
            }
        });
    </script>

    @yield('scripts')

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

</body>

</html>
