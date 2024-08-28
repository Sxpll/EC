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

    <!-- jQuery UI -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <style>
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
            position: fixed;
            top: 20px;
            right: 20px;
            cursor: pointer;
            font-size: 24px;
            color: #007bff;
            z-index: 1000;
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
            backdrop-filter: blur(10px);
        }

        .notification-banner.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .notification-banner.hide {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }

        .notifications-dropdown {
            display: none;
            position: fixed;
            top: 60px;
            right: 20px;
            width: 300px;
            background-color: rgba(0, 0, 0, 0.75);
            border: 1px solid #353333;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .notification-list {
            max-height: 200px;
            overflow-y: auto;
            padding: 10px;
        }

        .notification-item {
            padding: 10px;
            cursor: pointer;
        }

        .notification-item:hover {
            background-color: #858282;
            color: black;
        }
    </style>
</head>

<body>
    <div id="app">
        <div id="mySidebar" class="sidebar">
            @guest
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Register</a>
            @else
            <a href="{{ url('/home') }}">Home</a>
            <a href="{{ route('account.edit') }}">My Account</a>
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
            @endif
            @if (auth()->check() && auth()->user()->role == 'admin')
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

        <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo" style="height: 40px;">
                </a>
                @if(auth()->check() && auth()->user()->role === 'admin')
                <div id="notificationBell" class="notification-bell">
                    <span class="notification-count" id="notificationCount">0</span>
                    <i class="fa fa-bell"></i>
                </div>
                @endif
            </div>
        </nav>

        <main class="content-wrapper">
            @yield('content')
        </main>
    </div>

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

    <script>
        window.openChatWindow = function(chatId) {
            window.location.href = `/chat?openChat=${chatId}`;
        };

        document.addEventListener('DOMContentLoaded', function() {
            const notificationBell = document.getElementById('notificationBell');
            const notificationsDropdown = document.getElementById('notificationsDropdown');

            if (notificationBell) {
                notificationBell.addEventListener('click', function() {
                    notificationsDropdown.style.display = notificationsDropdown.style.display === 'block' ? 'none' : 'block';
                });
            }

            function fetchNotifications() {
                axios.get('/notifications')
                    .then(response => {
                        const notifications = response.data;
                        const notificationList = document.getElementById('notificationList');
                        const notificationCount = document.getElementById('notificationCount');
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

            window.addEventListener('click', function(event) {
                if (!notificationsDropdown.contains(event.target) && !notificationBell.contains(event.target)) {
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
</body>

</html>
