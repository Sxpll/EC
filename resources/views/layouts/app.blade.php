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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery UI -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
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
                    <!-- Ikonka edycji konta widoczna tylko dla zalogowanego użytkownika -->
                    @if(auth()->check())
                    <a href="{{ route('account.edit') }}"><i class="fa fa-user"></i></a>
                    @endif

                    <!-- Ikonka koszyka, widoczna zawsze -->
                    <a><i class="fa fa-shopping-cart"></i></a>

                    <!-- Ikonka dzwonka widoczna tylko dla zalogowanego admina -->
                    @if(auth()->check() && auth()->user()->role === 'admin')
                    <a id="notificationBell" href="#"><i class="fa fa-bell"></i><span id="notificationCount" class="notification-count" style="display: none;"></span></a>
                    @endif

                    <!-- Ikonka wylogowania widoczna tylko dla zalogowanego użytkownika -->
                    @if(auth()->check())
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-sign-out-alt"></i></a>
                    @endif
                </div>
            </div>
        </header>

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

    <!-- Notifications Dropdown -->
    <div id="notificationsDropdown" class="notifications-dropdown">
        <h6 class="dropdown-header">Notifications</h6>
        <div id="notificationList" class="notification-list"></div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/nestable2@1.6.0/dist/jquery.nestable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    @yield('scripts')

    @if(auth()->check() && auth()->user()->role === 'admin')
    <script>
        var notificationBannerShown = false;

        window.openChatWindow = function(chatId) {
            window.location.href = `/chat?openChat=${chatId}`;
        };

        document.addEventListener('DOMContentLoaded', function() {
            const notificationBell = document.getElementById('notificationBell');
            const notificationsDropdown = document.getElementById('notificationsDropdown');
            const notificationList = document.getElementById('notificationList');
            const notificationCount = document.getElementById('notificationCount');

            if (notificationBell && notificationsDropdown) {
                notificationBell.addEventListener('click', function() {
                    const isDisplayed = notificationsDropdown.style.display === 'block';
                    notificationsDropdown.style.display = isDisplayed ? 'none' : 'block';
                });
            }

            function fetchNotifications() {
                if (!notificationList) return;

                axios.get('/notifications')
                    .then(response => {
                        const notifications = response.data;
                        notificationList.innerHTML = '';

                        if (notifications.length > 0) {
                            if (notificationCount) {
                                notificationCount.style.display = 'inline-block';
                                notificationCount.innerText = notifications.length;
                            }
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
                            if (notificationCount) notificationCount.style.display = 'none';
                            notificationList.innerHTML = '<div class="text-center">No new notifications</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching notifications:', error);
                    });
            }

            fetchNotifications();
            setInterval(fetchNotifications, 5000);

            window.addEventListener('click', function(event) {
                if (notificationsDropdown && !notificationsDropdown.contains(event.target) && notificationBell && !notificationBell.contains(event.target)) {
                    notificationsDropdown.style.display = 'none';
                }
            });

            const urlParams = new URLSearchParams(window.location.search);
            const openChat = urlParams.get('openChat');
            if (openChat) {
                window.openChatWindow(openChat);
            }
        });
    </script>
    @endif

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

</body>

</html>
