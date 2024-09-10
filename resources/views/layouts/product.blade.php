<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Products</title>

    <!-- Import Bootstrapa tylko dla tego widoku -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Inne style potrzebne dla tego widoku -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Dodatkowe style dla widoku produktów -->
    <style>
        body {
            background-color: #1e1e2f;
            color: #ffffff;
            margin: 0;
            padding-top: 0;
            overflow-x: hidden;
        }

        .navbar {
            background-color: transparent !important;
            border: none;
            box-shadow: none !important;
        }

        .container-products {
            margin-left: 250px;
            margin-top: 20px;
        }

        .card {
            background-color: #2b2b3b;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin: 15px;
            width: 250px;
            height: 300px;
        }

        .product-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .card-title,
        .card-text {
            color: #ffffff;
        }

        .btn-outline-secondary {
            color: #ffffff;
            border-color: #1abc9c;
            margin-left: 10px;
        }

        .btn-outline-secondary:hover {
            background-color: #1abc9c;
            color: #ffffff;
            border-color: #16a085;
        }

        .form-control {
            margin-left: 300px;
            border-radius: 5px;
            margin-right: 300px;
        }

        .add-to-cart {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            color: #1abc9c;
            cursor: pointer;
            transition: color 0.3s;
        }

        .add-to-cart:hover {
            color: #16a085;
        }

        .search-bar {
            margin: 20px;
            margin-left: 250px;
        }

        @media (max-width: 768px) {
            .container-products {
                margin-left: 0;
                padding: 10px;
            }

            .sidebar {
                position: fixed;
                width: 200px;
                height: 100%;
                overflow-y: auto;
                z-index: 1000;
                background-color: #2b2b3b;
                transition: all 0.3s ease-in-out;
            }

            .navbar {
                position: fixed;
                width: 100%;
                top: 0;
                left: 0;
                z-index: 1001;
                background-color: #1e1e2f;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            main {
                margin-top: 60px;
                margin-left: 0;
                padding: 10px;
            }

            .search-bar {
                margin-left: 10px;
                margin-right: 10px;
            }
        }
    </style>

    <!-- Skrypty -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>

<body>
    <div id="app">
        <!-- Sidebar -->
        <div id="mySidebar" class="sidebar">
            @guest
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Register</a>
            @else
            <a href="{{ url('/home') }}">Home</a>
            <a href="{{ route('products.publicIndex') }}">Products</a>
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

        <!-- Navbar -->
        <nav class="navbar navbar-expand-md">
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

        <!-- Wyszukiwarka -->
        <div class="search-bar">
            <form action="{{ route('products.publicIndex') }}" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search products..."
                        value="{{ request()->input('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                </div>
            </form>
        </div>

        <!-- Główna zawartość -->
        <main class="container-products py-4">
            <div class="product-grid">
                @foreach($products as $product)
                <div class="col-md-3 mb-4 d-flex align-items-stretch position-relative">
                    <div class="card h-100 shadow-sm rounded">
                        @if($product->images->count())
                        <img src="data:{{ $product->images->first()->mime_type }};base64,{{ $product->images->first()->file_data }}" class="card-img-top" alt="{{ $product->name }}" style="height: 150px; object-fit: cover;">
                        @else
                        <img src="https://via.placeholder.com/150" class="card-img-top" alt="{{ $product->name }}" style="height: 150px; object-fit: cover;">
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text">{{ Str::limit($product->description, 100) }}</p>
                        </div>
                        <!-- Ikonka koszyka -->
                        <i class="fas fa-shopping-cart add-to-cart"></i>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Paginacja -->
            <div class="d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        </main>
    </div>

    <!-- Dropdown z powiadomieniami -->
    <div id="notificationsDropdown" class="notifications-dropdown">
        <h6 class="dropdown-header">Notifications</h6>
        <div id="notificationList" class="notification-list"></div>
    </div>

    <!-- Skrypty Bootstrapa -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>

    <!-- Inne skrypty -->
    <script src="https://cdn.jsdelivr.net/npm/nestable2@1.6.0/dist/jquery.nestable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    @yield('scripts')

    <!-- Skrypty dla powiadomień -->
    @if(auth()->check() && auth()->user()->role === 'admin')
    <script>
        var notificationBannerShown = false;

        window.openChatWindow = function(chatId) {
            window.location.href = `/chat?openChat=${chatId}`;
        };

        document.addEventListener('DOMContentLoaded', function() {
            const notificationBell = document.getElementById('notificationBell');
            const notificationsDropdown = document.getElementById('notificationsDropdown');

            if (notificationBell) {
                notificationBell.addEventListener('click', function() {
                    const isDisplayed = notificationsDropdown.style.display === 'block';
                    notificationsDropdown.style.display = isDisplayed ? 'none' : 'block';
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
    @endif
</body>

</html>
