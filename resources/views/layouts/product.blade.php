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

    <!-- Skrypty -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
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

        <!-- Wyszukiwarka -->
        <div class="search-bar">
            <form action="{{ route('products.publicIndex') }}" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request()->input('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                </div>
            </form>
        </div>

        <!-- Główna zawartość -->
        <main class="container-products py-4">
            <div class="product-grid">
                @foreach($products as $product)
                <div class="d-flex align-items-stretch position-relative">
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

    <!-- Inne skrypty -->
    <script src="https://cdn.jsdelivr.net/npm/nestable2@1.6.0/dist/jquery.nestable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

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
