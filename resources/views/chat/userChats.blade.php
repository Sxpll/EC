@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="elo">Your Threads</h1>
        <div class="costest">
            <button id="newThreadButton" class="new-chat-button">New Chat</button>
            <select id="chatStatusFilter" class="form-control ml-3" style="max-width: 200px; margin-left: 10px;">
                <option value="open" selected>Open</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>
    <div class="chat-container">
        <ul class="list-group mt-3" id="chatList">
            <!-- Lista czatów będzie ładowana dynamicznie za pomocą JavaScript -->
        </ul>
    </div>
</div>


<!-- New Chat Modal -->
<div id="newChatModal" class="modal" tabindex="-1" role="dialog" style="display:none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Chat</h5>
                <button type="button" class="close-custom" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createChatForm" action="{{ route('chat.createChat') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" name="title" id="title" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Start Chat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Chat Window Modal -->
<div id="chatWindowModal" class="modal" tabindex="-1" role="dialog" style="display:none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chatTitle">Chat</h5>
                <button type="button" class="close-custom" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="chat-window" style="height: 50vh; overflow-y: scroll;">
                <!-- Messages will be loaded here -->
            </div>
            <div class="modal-footer">
                <form id="sendMessageForm" action="" method="POST">
                    @csrf
                    <textarea name="message" rows="3" class="form-control" style="resize: none;" required></textarea>
                    <button type="submit" class="btn btn-primary mt-2">Send</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const newThreadButton = document.getElementById('newThreadButton');
        const createChatForm = document.getElementById('createChatForm');
        const newChatModal = document.getElementById('newChatModal');
        const chatWindowModal = document.getElementById('chatWindowModal');
        const chatWindow = document.getElementById('chat-window');
        const chatTitle = document.getElementById('chatTitle');
        const chatStatusFilter = document.getElementById('chatStatusFilter');
        const chatList = document.getElementById('chatList');

        function loadChats(status) {
            axios.get(`/chat/filter`, {
                    params: {
                        status: status
                    }
                })
                .then(response => {
                    chatList.innerHTML = '';
                    response.data.forEach(chat => {
                        const chatItem = document.createElement('li');
                        chatItem.classList.add('list-group-item', 'chat-item');
                        chatItem.setAttribute('data-status', chat.status);
                        chatItem.innerHTML = `
                        <a href="#" class="chat-link" data-chat-id="${chat.id}">
                            <div class="chat-title">${chat.title}</div>
                            <div class="chat-time">${new Date(chat.created_at).toLocaleString()}</div>
                        </a>
                    `;
                        chatList.appendChild(chatItem);

                        const chatLink = chatItem.querySelector('.chat-link');
                        if (chatLink) {
                            chatLink.addEventListener('click', function(e) {
                                e.preventDefault();
                                openChatWindow(chat.id);
                            });
                        }
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while filtering chats. Please try again.');
                });
        }

        // Load default open chats on page load
        if (chatStatusFilter) {
            loadChats(chatStatusFilter.value);
        }

        // Handle status filter change
        if (chatStatusFilter) {
            chatStatusFilter.addEventListener('change', function() {
                loadChats(this.value);
            });
        }

        if (newThreadButton) {
            newThreadButton.addEventListener('click', function() {
                if (newChatModal) {
                    newChatModal.style.display = 'block';
                }
            });
        }

        // Close modals
        document.querySelectorAll('.close-custom').forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) {
                    modal.style.display = 'none';
                }
            });
        });

        // Create new chat
        if (createChatForm) {
            createChatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                axios.post(this.action, formData)
                    .then(response => {
                        if (response.data.success) {
                            let newChatItem = document.createElement('li');
                            newChatItem.classList.add('list-group-item', 'chat-item');
                            newChatItem.setAttribute('data-status', 'open');
                            newChatItem.innerHTML = `
                            <a href="#" class="chat-link" data-chat-id="${response.data.chat.id}">
                                <div class="chat-title">${response.data.chat.title}</div>
                                <div class="chat-time">${new Date(response.data.chat.created_at).toLocaleString()}</div>
                            </a>
                        `;
                            chatList.insertBefore(newChatItem, chatList.firstChild);
                            newChatModal.style.display = 'none';
                            document.getElementById('title').value = '';

                            const chatLink = newChatItem.querySelector('.chat-link');
                            if (chatLink) {
                                chatLink.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    openChatWindow(response.data.chat.id);
                                });
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        }

        function openChatWindow(chatId) {
            let url = `/chat/${chatId}`;
            axios.get(url)
                .then(response => {
                    chatWindow.innerHTML = '';

                    if (response.data && response.data.messages) {
                        let messages = response.data.messages;
                        if (messages.length > 0 && messages[0].chat) {
                            chatTitle.textContent = messages[0].chat.title;
                        } else {
                            chatTitle.textContent = 'Chat';
                        }
                        messages.forEach(msg => {
                            let messageDiv = document.createElement('div');
                            let messageClass = msg.admin_id ? 'admin' : 'user';
                            messageDiv.classList.add('message', messageClass);
                            messageDiv.innerHTML = `${msg.message}`;
                            let messageTime = document.createElement('div');
                            messageTime.classList.add('message-time');
                            messageTime.textContent = new Date(msg.created_at).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            messageTime.style.display = 'none';
                            messageDiv.appendChild(messageTime);
                            messageDiv.addEventListener('click', () => {
                                messageTime.style.display = messageTime.style.display === 'block' ? 'none' : 'block';
                            });
                            chatWindow.appendChild(messageDiv);
                        });
                    } else {
                        console.error('No messages found or no chat data in response:', response.data);
                        chatTitle.textContent = 'Chat';
                    }

                    const sendMessageForm = document.getElementById('sendMessageForm');
                    if (sendMessageForm) {
                        sendMessageForm.action = url + '/send-message';
                    }
                    chatWindowModal.style.display = 'block';
                    chatWindow.scrollTop = chatWindow.scrollHeight;
                    startAutoRefresh(chatId);
                })
                .catch(error => console.error('Error:', error));
        }

        let refreshInterval = null;

        function startAutoRefresh(chatId) {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }

            refreshInterval = setInterval(() => {
                fetch(`/chat/${chatId}/messages`)
                    .then(response => response.json())
                    .then(messages => {
                        chatWindow.innerHTML = '';
                        messages.forEach(msg => {
                            let messageDiv = document.createElement('div');
                            let messageClass = msg.admin_id ? 'admin' : 'user';
                            messageDiv.classList.add('message', messageClass);
                            messageDiv.innerHTML = `${msg.message}`;
                            let messageTime = document.createElement('div');
                            messageTime.classList.add('message-time');
                            messageTime.textContent = new Date(msg.created_at).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            messageTime.style.display = 'none';
                            messageDiv.appendChild(messageTime);
                            messageDiv.addEventListener('click', () => {
                                messageTime.style.display = messageTime.style.display === 'block' ? 'none' : 'block';
                            });
                            chatWindow.appendChild(messageDiv);
                        });
                        chatWindow.scrollTop = chatWindow.scrollHeight;
                    })
                    .catch(error => console.error('Error refreshing messages:', error));
            }, 3000);
        }

        const sendMessageForm = document.getElementById('sendMessageForm');
        if (sendMessageForm) {
            sendMessageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                let message = this.message.value;
                axios.post(this.action, {
                        message: message
                    })
                    .then(response => {
                        if (response.data.success) {
                            let newMessage = document.createElement('div');
                            newMessage.classList.add('message', 'user');
                            newMessage.innerHTML = `${message}`;
                            let messageTime = document.createElement('div');
                            messageTime.classList.add('message-time');
                            messageTime.textContent = new Date().toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            messageTime.style.display = 'none';
                            newMessage.appendChild(messageTime);
                            newMessage.addEventListener('click', () => {
                                messageTime.style.display = messageTime.style.display === 'block' ? 'none' : 'block';
                            });
                            chatWindow.appendChild(newMessage);
                            this.message.value = '';
                            chatWindow.scrollTop = chatWindow.scrollHeight;
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        }
    });
</script>
@endsection
