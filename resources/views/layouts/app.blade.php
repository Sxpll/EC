<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Meta Data -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts and Styles -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/responsive.css') }}" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Axios Library -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Przekazanie URL trasy do JavaScript -->
    <script type="text/javascript">
        const cartContentsUrl = "{{ route('cart.contents') }}";
    </script>
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
                    @if(auth()->check() && auth()->user()->role == 'admin')
                    <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
                    @endif
                    <a href="{{ route('chat.index') }}">Chat</a>
                </nav>

                <div class="navbar-icons">
                    <!-- Theme Switch -->
                    <div class="theme-switch">
                        <input type="checkbox" id="theme-toggle-navbar" class="theme-toggle-input">
                        <label for="theme-toggle-navbar" class="theme-toggle-label">
                            <span class="theme-icon theme-sun"><i class="fas fa-sun"></i></span>
                            <span class="theme-icon theme-moon"><i class="fas fa-moon"></i></span>
                        </label>
                    </div>

                    <!-- Account Icon -->
                    <a href="{{ route('account.edit') }}" class="account-icon"><i class="fa fa-user"></i></a>

                    <!-- Cart Icon -->
                    <div class="cart-container">
                        <a href="#" id="cartIcon"><i class="fa fa-shopping-cart"></i></a>
                        <span id="cartItemCount" class="cart-item-count" style="display: none;">0</span>

                        <!-- Cart Dropdown -->
                        <div id="cartDropdown" class="cart-dropdown">
                            <h6 class="dropdown-header">Twój koszyk</h6>
                            <div id="cartList" class="cart-list"></div>
                            <div class="cart-total">
                                <strong>Łączna kwota:</strong> <span id="cartTotal">0.00</span> zł
                            </div>
                            <a href="{{ route('cart.index') }}" class="btn btn-primary btn-block">Przejdź do koszyka</a>
                        </div>
                    </div>

                    <!-- Notifications Icon -->
                    @if(auth()->check() && auth()->user()->role == 'admin')
                    <a href="#" id="notificationBell"><i class="fa fa-bell"></i><span id="notificationCount" class="notification-count" style="display: none;"></span></a>
                    @endif

                    <!-- Logout Icon -->
                    @if(auth()->check())
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="logout-icon"><i class="fa fa-sign-out-alt"></i></a>
                    @endif

                    <!-- Hamburger Menu -->
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
                @if(auth()->check() && auth()->user()->role == 'admin')
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
        @if(auth()->check() && auth()->user()->role == 'admin')
        <div id="notificationsDropdown" class="notifications-dropdown">
            <h6 class="dropdown-header">Notifications</h6>
            <div id="notificationList" class="notification-list"></div>
        </div>
        @endif

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
        document.addEventListener('DOMContentLoaded', function() {
            // Theme switch logic
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

            // Hamburger and sidebar functionality
            document.getElementById('navbar-toggler').addEventListener('click', function() {
                document.getElementById('sidebar').classList.add('open');
            });

            document.getElementById('close-sidebar').addEventListener('click', function() {
                document.getElementById('sidebar').classList.remove('open');
            });

            // Notifications functionality


            document.getElementById('notificationBell').addEventListener('click', function(event) {
                event.preventDefault();
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

        

            // Cart functionality
            const cartContentsUrl = "{{ route('cart.contents') }}";

            function updateCartItemCount() {
                axios.get(cartContentsUrl)
                    .then(response => {
                        const cart = response.data.cart;
                        const itemCount = Object.keys(cart).length;
                        const cartItemCount = document.getElementById('cartItemCount');
                        if (cartItemCount) {
                            cartItemCount.textContent = itemCount;
                            cartItemCount.style.display = itemCount > 0 ? 'inline-block' : 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Błąd podczas aktualizacji liczby produktów w koszyku:', error);
                    });
            }

            // Wywołaj funkcję po załadowaniu strony
            updateCartItemCount();

            const cartIcon = document.getElementById('cartIcon');
            const cartDropdown = document.getElementById('cartDropdown');

            if (cartIcon) {
                cartIcon.addEventListener('click', function(event) {
                    event.preventDefault();
                    const isDisplayed = cartDropdown.style.display === 'block';
                    cartDropdown.style.display = isDisplayed ? 'none' : 'block';
                    if (!isDisplayed) {
                        fetchCartContents();
                    }
                });
            }

            // Funkcja fetchCartContents()
            function fetchCartContents() {
                const cartList = document.getElementById('cartList');
                const cartTotal = document.getElementById('cartTotal');

                axios.get(cartContentsUrl)
                    .then(response => {
                        const cart = response.data.cart;
                        const total = response.data.total;
                        cartList.innerHTML = '';

                        if (Object.keys(cart).length > 0) {
                            for (const id in cart) {
                                const item = cart[id];
                                const itemDiv = document.createElement('div');
                                itemDiv.classList.add('cart-item');
                                itemDiv.innerHTML = `
                                    <div class="cart-item-name">${item.name}</div>
                                    <div class="cart-item-details">${item.quantity} x ${parseFloat(item.price).toFixed(2)} zł</div>
                                `;
                                cartList.appendChild(itemDiv);
                            }
                            cartTotal.innerText = parseFloat(total).toFixed(2);
                        } else {
                            cartList.innerHTML = '<div class="text-center">Koszyk jest pusty</div>';
                            cartTotal.innerText = '0.00';
                        }
                    })
                    .catch(error => {
                        console.error('Błąd podczas pobierania zawartości koszyka:', error);
                    });
            }

            // Zamknięcie dropdownu koszyka po kliknięciu poza nim
            window.addEventListener('click', function(event) {
                if (cartDropdown && !cartDropdown.contains(event.target) && cartIcon && !cartIcon.contains(event.target)) {
                    cartDropdown.style.display = 'none';
                }
            });

            // Dodaj event listener do formularzy 'Dodaj do koszyka'
            const addToCartForms = document.querySelectorAll('.add-to-cart-form');

            addToCartForms.forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    const formData = new FormData(form);
                    const action = form.getAttribute('action');

                    axios.post(action, formData)
                        .then(response => {
                            updateCartItemCount();
                            alert('Produkt dodany do koszyka!');
                        })
                        .catch(error => {
                            console.error('Błąd podczas dodawania do koszyka:', error);
                        });
                });
            });
        });
    </script>

    @yield('scripts')

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</body>

</html>
