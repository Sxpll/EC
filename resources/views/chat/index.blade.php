@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="elo">All Chats</h1>
    <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chats as $chat)
                    <tr data-chat-id="{{ $chat->id }}">
                        <td>{{ $chat->title }}</td>
                        <td>{{ $chat->user->name }} {{ $chat->user->surname }}</td>
                        <td>{{ $chat->status }}</td>
                        <td class="text-center">
                            <button class="btn btn-primary btn-view" data-chat-id="{{ $chat->id }}">View</button>
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
        <span class="close-custom">&times;</span>
        <div class="modal-header d-flex justify-content-between align-items-center">
            <h5 class="chat-title" id="chatTitle">Chat</h5>
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
                <textarea name="message" rows="1" class="form-control" placeholder="Type your message..." required></textarea>
                <button type="submit" class="btn btn-primary mt-2">Send</button>
            </form>
        </div>
    </div>
</div>

<!-- Manage Modal -->
<div id="manageModal" class="modal">
    <div class="modal-content">
        <span class="close-custom">&times;</span>
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
    const userId = @json(Auth::id()); // Bezpieczne przekazanie identyfikatora użytkownika
    let currentChatId = null;
    let refreshInterval = null;

    document.querySelectorAll('.btn-view').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            currentChatId = this.getAttribute('data-chat-id');
            if (!currentChatId) {
                console.error('Chat ID is not defined');
                return;
            }
            openChatWindow(currentChatId);
        });
    });

    function openChatWindow(chatId) {
        fetch(`/chat/${chatId}`)
            .then(response => response.json())
            .then(data => {
                chatWindow.innerHTML = '';
                const messages = data.messages;
                messages.forEach(msg => {
                    let messageDiv = document.createElement('div');
                    let messageClass = (msg.admin_id === userId) ? 'user' : 'admin';
                    messageDiv.classList.add('message', messageClass);
                    messageDiv.innerHTML = `${msg.message} <span class="message-time">${new Date(msg.created_at).toLocaleTimeString()}</span>`;
                    chatWindow.appendChild(messageDiv);
                    messageDiv.addEventListener('click', () => {
                        const timeSpan = messageDiv.querySelector('.message-time');
                        timeSpan.style.display = timeSpan.style.display === 'block' ? 'none' : 'block';
                    });
                });

                sendMessageForm.action = `/chat/${chatId}/send-message`;
                chatWindowModal.style.display = 'block';
                chatWindow.scrollTop = chatWindow.scrollHeight;
                startAutoRefresh(chatId);
            })
            .catch(error => {
                console.error('Error fetching chat messages:', error);
            });
    }

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
                        let messageClass = (msg.admin_id === userId) ? 'user' : 'admin';
                        messageDiv.classList.add('message', messageClass);
                        messageDiv.innerHTML = `${msg.message} <span class="message-time">${new Date(msg.created_at).toLocaleTimeString()}</span>`;
                        chatWindow.appendChild(messageDiv);
                    });
                    chatWindow.scrollTop = chatWindow.scrollHeight;
                })
                .catch(error => console.error('Error refreshing messages:', error));
        }, 3000);
    }

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
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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

    document.querySelectorAll('.close-custom').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    sendMessageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!currentChatId) {
            console.error('Chat ID is not set');
            return;
        }
        let message = messageInput.value;
        fetch(this.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let newMessage = document.createElement('div');
                newMessage.classList.add('message', 'user');
                newMessage.innerHTML = `${message} <span class="message-time">${new Date().toLocaleTimeString()}</span>`;
                chatWindow.appendChild(newMessage);
                messageInput.value = '';
                chatWindow.scrollTop = chatWindow.scrollHeight;
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
});

</script>
@endsection
