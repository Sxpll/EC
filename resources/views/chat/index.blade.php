@extends('layouts.app')

@section('content')

<div id="notificationsDropdown" class="notifications-dropdown">
    <h6 class="dropdown-header">Notifications</h6>
    <div id="notificationList" class="notification-list"></div>
</div>

<div class="container">
    <h1 class="elo">Wiadomości</h1>
    <div class="form-group mb-3">
        <input type="text" id="searchChat" class="form-control" placeholder="Szukaj...">
    </div>

    <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Tytuł</th>
                    <th>Autor</th>
                    <th>Status</th>
                    <th class="text-center">Akcje</th>
                    <th class="text-center">Powiadomienia</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chats as $chat)
                <tr data-chat-id="{{ $chat->id }}">
                    <td>{{ $chat->title }}</td>
                    <td>{{ $chat->user->name }} {{ $chat->user->surname }}</td>
                    <td>{{ $chat->status }}</td>
                    <td class="text-center">
                        <button class="btn btn-primary btn-view" data-chat-id="{{ $chat->id }}">Pokaż</button>
                    </td>
                    <td class="text-center">
                        @php
                        $unreadMessagesCount = $chat->messages->where('is_read', false)->count();
                        $dotColor = '';

                        if ($unreadMessagesCount > 0) {
                        if ($chat->admin_id === null) {
                        $dotColor = 'text-white';
                        } elseif ($chat->admin_id !== Auth::id()) {
                        $dotColor = 'text-primary';
                        } elseif ($chat->admin_id === Auth::id()) {
                        $dotColor = 'text-success';
                        }
                        }
                        @endphp

                        @if ($unreadMessagesCount > 0)
                        <i class="fas fa-circle {{ $dotColor }}" data-chat-id="{{ $chat->id }}" id="chat-dot-{{ $chat->id }}"></i>
                        @else
                        <i class="fas fa-circle" data-chat-id="{{ $chat->id }}" id="chat-dot-{{ $chat->id }}" style="display:none;"></i>
                        @endif
                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</div>

<!-- Chat Modal -->
<div id="chatWindowModal" class="modal">
    <div class="modal-content">
        <div class="modal-header d-flex justify-content-between align-items-center">
            <h5 class="chat-title" id="chatTitle">Czat</h5>
            @if(Auth::user()->is_hr)
            <button id="manageButton" class="btn btn-primary">Manage</button>
            @endif
        </div>
        <div class="modal-body" id="chat-window" style="height: 50vh; overflow-y: scroll;">
            <!-- Messages will be loaded here -->
        </div>
        <div class="modal-footer">
            <form id="sendMessageForm" action="" method="POST" class="chat-input">
                @csrf
                <textarea name="message" rows="1" class="form-control" placeholder="Wpisz wiadomość..." required></textarea>
                <button type="submit" class="btn btn-primary mt-2">Wyślij</button>
            </form>
        </div>
    </div>
</div>

<!-- Manage Modal -->
<div id="manageModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5>Manage Chat</h5>
        </div>
        <div class="modal-body">
            <form id="manageForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" class="form-control">
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="form-group d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
                <div class="form-group d-flex justify-content-end">
                    <button type="button" id="takeChatButton" class="btn btn-primary">Take Chat</button>
                </div>
            </form>
        </div>
    </div>
</div>





<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatWindowModal = document.getElementById('chatWindowModal');
        const chatWindow = document.getElementById('chat-window');
        const manageModal = document.getElementById('manageModal');
        const sendMessageForm = document.getElementById('sendMessageForm');
        const messageInput = sendMessageForm.querySelector('textarea[name="message"]');
        const manageButton = document.getElementById('manageButton');
        const takeChatButton = document.getElementById('takeChatButton');
        const chatTableBody = document.querySelector('tbody');
        const searchChat = document.getElementById('searchChat');
        const userId = @json(Auth::id());
        let currentChatId = null;
        let refreshInterval = null;
        let chatDotsRefreshInterval = null;

        function openChatWindow(chatId) {
            fetch(`/chat/${chatId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data || !data.messages) {
                        console.error('Invalid data format:', data);
                        return;
                    }

                    const isAdmin = data.is_admin;
                    chatWindow.innerHTML = '';
                    const messages = data.messages;
                    messages.forEach(msg => {
                        let messageDiv = document.createElement('div');
                        let messageClass = (msg.user && msg.user.id === userId) ? 'user' : 'admin';

                        messageDiv.classList.add('message', messageClass);

                        let senderName = 'Unknown';

                        if (msg.admin_id && msg.admin_id === userId) {
                            // Wyświetl "You" dla wiadomości wysłanych przez aktualnego admina
                            senderName = 'You';
                        } else if (isAdmin) {
                            // Dla admina: pokaż imię i nazwisko wszystkich
                            senderName = msg.user ? `${msg.user.name} ${msg.user.lastname}` : 'Unknown';
                        } else {
                            // Dla użytkownika: pokaż "Admin" dla wiadomości admina
                            if (msg.admin_id) {
                                senderName = 'Admin';
                            } else if (msg.user && msg.user.id === userId) {
                                // Jeśli wiadomość pochodzi od zalogowanego użytkownika, nie pokazuj etykiety
                                senderName = '';
                            } else {
                                // W przeciwnym razie, pokaż imię i nazwisko użytkownika
                                senderName = msg.user ? `${msg.user.name} ${msg.user.lastname}` : 'Unknown';
                            }
                        }

                        const messageContent = senderName ? `<strong>${senderName}:</strong> ${msg.message} <span class="message-time">${new Date(msg.created_at).toLocaleTimeString()}</span>` : `${msg.message} <span class="message-time">${new Date(msg.created_at).toLocaleTimeString()}</span>`;

                        messageDiv.innerHTML = messageContent;
                        chatWindow.appendChild(messageDiv);
                    });

                    sendMessageForm.action = `/chat/${chatId}/send-message`;
                    chatWindowModal.style.display = 'block';
                    chatWindow.scrollTop = chatWindow.scrollHeight; // Przewiń na dół
                    currentChatId = chatId;
                    startAutoRefresh(chatId);
                    markAllNotificationsAsRead(chatId);
                })
                .catch(error => {
                    console.error('Error fetching chat messages:', error);
                });
        }

        window.openChatWindow = openChatWindow;

        function bindViewButtons() {
            document.querySelectorAll('.btn-view').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const chatId = this.getAttribute('data-chat-id');
                    if (!chatId) {
                        console.error('Chat ID is not defined');
                        return;
                    }
                    openChatWindow(chatId);
                });
            });
        }

        bindViewButtons();

        window.addEventListener('click', function(event) {
            if (event.target === chatWindowModal) {
                chatWindowModal.style.display = 'none';
                clearInterval(refreshInterval);
                updateChatDots();
            }

            if (event.target === manageModal) {
                manageModal.style.display = 'none';
            }
        });

        function startAutoRefresh(chatId) {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }

            refreshInterval = setInterval(() => {
                const previousScrollHeightMinusTop = chatWindow.scrollHeight - chatWindow.scrollTop;

                fetch(`/chat/${chatId}/messages`)
                    .then(response => response.json())
                    .then(messages => {
                        chatWindow.innerHTML = '';
                        messages.forEach(msg => {
                            let messageDiv = document.createElement('div');
                            let messageClass = (msg.user && msg.user.id === userId) ? 'user' : 'admin';

                            messageDiv.classList.add('message', messageClass);

                            const senderName = msg.admin_id === userId ? 'You' : (msg.user ? `${msg.user.name} ${msg.user.lastname}` : 'Unknown');
                            const messageContent = `<strong>${senderName}:</strong> ${msg.message} <span class="message-time">${new Date(msg.created_at).toLocaleTimeString()}</span>`;

                            messageDiv.innerHTML = messageContent;
                            chatWindow.appendChild(messageDiv);
                        });

                        chatWindow.scrollTop = chatWindow.scrollHeight - previousScrollHeightMinusTop;
                    })
                    .catch(error => console.error('Error refreshing messages:', error));
            }, 3000);
        }

        function updateChatDots() {
            fetch('/chat', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(chats => {
                    chats.forEach(chat => {
                        const dotElement = document.querySelector(`#chat-dot-${chat.id}`);
                        if (dotElement) {
                            const unreadMessagesCount = chat.messages.filter(msg => !msg.is_read).length;

                            if (unreadMessagesCount > 0) {
                                if (chat.admin_id === null) {
                                    dotElement.className = 'fas fa-circle text-white';
                                    dotElement.style.display = '';
                                } else if (chat.admin_id !== userId) {
                                    dotElement.className = 'fas fa-circle text-primary';
                                    dotElement.style.display = '';
                                } else if (chat.admin_id === userId) {
                                    dotElement.className = 'fas fa-circle text-success';
                                    dotElement.style.display = '';
                                }
                            } else {
                                dotElement.style.display = 'none';
                            }
                        }
                    });
                })
                .catch(error => console.error('Error updating chat dots:', error));
        }

        function startChatDotsRefresh() {
            if (chatDotsRefreshInterval) {
                clearInterval(chatDotsRefreshInterval);
            }

            chatDotsRefreshInterval = setInterval(updateChatDots, 5000);
        }

        startChatDotsRefresh();

        sendMessageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!currentChatId) {
                console.error('Chat ID is not set');
                return;
            }
            let message = messageInput.value;
            fetch(sendMessageForm.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        message: message
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let newMessage = document.createElement('div');
                        newMessage.classList.add('message', 'user');

                        const senderName = 'You';
                        newMessage.innerHTML = `<strong>${senderName}:</strong> ${message} <span class="message-time">${new Date().toLocaleTimeString()}</span>`;

                        chatWindow.appendChild(newMessage);
                        messageInput.value = '';
                        chatWindow.scrollTop = chatWindow.scrollHeight;
                        updateChatDots();
                    } else {
                        toastr.error('Failed to send message.');
                    }
                })
                .catch(error => {
                    console.error('Error sending message:', error);
                    toastr.error('Error sending message.');
                });
        });

        sendMessageForm.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessageForm.dispatchEvent(new Event('submit'));
            }
        });

        if (manageButton) {
            manageButton.addEventListener('click', function() {
                if (!currentChatId) {
                    console.error('Chat ID is not set');
                    return;
                }
                document.getElementById('manageForm').action = `/chat/${currentChatId}/manage`;
                manageModal.style.display = 'block';
            });
        }

        takeChatButton.addEventListener('click', function() {
            if (!currentChatId) {
                console.error('Chat ID is not set');
                return;
            }
            fetch(`/chat/${currentChatId}/take`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success('Chat has been taken successfully.');
                        manageModal.style.display = 'none';
                        chatWindowModal.style.display = 'none';
                        location.reload();
                    } else {
                        toastr.error('Failed to take chat.');
                    }
                })
                .catch(error => {
                    console.error('Error taking chat:', error);
                    toastr.error('Error taking chat.');
                });
        });

        function markAllNotificationsAsRead(chatId) {
            axios.post(`/notifications/mark-all-as-read`, {
                    chat_id: chatId
                })
                .then(response => {
                    fetchNotifications();
                })
                .catch(error => {
                    console.error('Error marking notifications as read:', error);
                });
        }

        function fetchNotifications() {
            axios.get('/notifications')
                .then(response => {
                    const notifications = response.data;
                    updateNotificationUI(notifications);
                    checkForNewMessages(notifications);
                })
                .catch(error => {
                    console.error('Error fetching notifications:', error);
                });
        }

        function updateNotificationUI(notifications) {
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
                        openChatWindow(notification.chat_id);
                        markAllNotificationsAsRead(notification.chat_id);
                    });
                    notificationList.appendChild(item);
                });
            } else {
                notificationCount.style.display = 'none';
                notificationList.innerHTML = '<div class="text-center">No new notifications</div>';
            }
        }

        function checkForNewMessages(notifications) {
            if (!notificationBannerShown) {
                notifications.forEach(notification => {
                    if (!notification.read) {
                        showNotificationBanner(notification.message);
                        notificationBannerShown = true;
                    }
                });
            }
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

        searchChat.addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();

            fetch(`/chat?search=${encodeURIComponent(searchValue)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    return response.text();
                })
                .then(data => {
                    try {
                        const chats = JSON.parse(data);
                        chatTableBody.innerHTML = '';

                        chats.forEach(chat => {
                            const chatRow = document.createElement('tr');
                            chatRow.setAttribute('data-chat-id', chat.id);
                            chatRow.innerHTML = `
                            <td>${chat.title}</td>
                            <td>${chat.user.name} ${chat.user.lastname}</td>
                            <td>${chat.status}</td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-view" data-chat-id="${chat.id}">View</button>
                            </td>
                        `;
                            chatTableBody.appendChild(chatRow);
                        });

                        bindViewButtons();
                    } catch (error) {
                        console.error('Error parsing JSON:', error);
                    }
                })
                .catch(error => console.error('Error fetching chats:', error));
        });

        setInterval(fetchNotifications, 5000);
        fetchNotifications();
    });
</script>
@endsection
