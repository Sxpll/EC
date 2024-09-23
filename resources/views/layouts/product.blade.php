<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Products</title>

    <!-- Bootstrap & Styles -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div id="app">
        <!-- Navbar -->
        <header>
            <div class="navbar-container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo">
                </a>
                <nav class="navbar-links">
                    <a href="{{ url('/home') }}">Home</a>
                    <a href="{{ route('products.publicIndex') }}">Products</a>
                    @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
                    @endif
                    <a href="{{ route('chat.index') }}">Chat</a>
                </nav>
                <div class="navbar-icons">
                    <a href="{{ route('account.edit') }}"><i class="fa fa-user"></i></a>
                    <a><i class="fa fa-shopping-cart"></i></a>
                    <a id="notificationBell" href="#"><i class="fa fa-bell"></i></a>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-sign-out-alt"></i></a>
                </div>
            </div>
        </header>

        <!-- Wyszukiwarka i sortowanie -->
        <div class="search-bar">
            <form action="{{ route('products.publicIndex') }}" method="GET">
                <input type="text" name="search" placeholder="Search products..." value="{{ request()->input('search') }}">
                <button type="submit">Search</button>
            </form>
            <select>
                <option value="">Sort by</option>
                <option value="price">Price</option>
                <option value="name">Name</option>
            </select>
        </div>

        <!-- Sidebar z filtrami -->
        <div class="floating-sidebar shadow">
            <h3>Filters & Categories</h3>
            <!-- Przyszłe funkcje: wybór kategorii, filtry -->
        </div>

        <!-- Główna sekcja z produktami -->
        <main class="products-section">
            <div class="row product-grid" id="product-grid">
                @foreach($products as $product)
                <div class="col-lg-2 col-md-3 col-sm-4 mb-4 d-flex align-items-stretch">
                    <div class="card product-card">
                        @if($product->images->count())
                        <img src="data:{{ $product->images->first()->mime_type }};base64,{{ $product->images->first()->file_data }}" class="card-img-top" alt="{{ $product->name }}">
                        @else
                        <img src="https://via.placeholder.com/150" class="card-img-top" alt="{{ $product->name }}">
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text">{{ Str::limit($product->description, 60) }}</p>
                        </div>
                        <div class="card-footer text-center">
                            <i class="fas fa-shopping-cart"></i>
                            <button class="btn btn-primary">Add to cart</button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
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

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <!-- Skrypty Bootstrapa -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>

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
