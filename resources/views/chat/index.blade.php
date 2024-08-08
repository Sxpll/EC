@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="elo">All Chats</h1>
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
                <tr>
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
@vite('resources/js/app.js')



<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatWindowModal = document.getElementById('chatWindowModal');
    const chatWindow = document.getElementById('chat-window');
    const manageModal = document.getElementById('manageModal');
    const sendMessageForm = document.getElementById('sendMessageForm');
    const messageInput = sendMessageForm.querySelector('textarea[name="message"]');
    const manageButton = document.getElementById('manageButton');
    const takeChatButton = document.getElementById('takeChatButton');
    let currentChatId = null;

    document.querySelectorAll('.btn-view').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            currentChatId = this.getAttribute('data-chat-id');
            console.log('Current Chat ID:', currentChatId); // Debugging line
            if (!currentChatId) {
                console.error('Chat ID is not defined');
                return;
            }
            let url = `/chat/${currentChatId}`;
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    chatWindow.innerHTML = '';
                    const messages = data.messages;
                    messages.forEach(msg => {
                        let messageDiv = document.createElement('div');
                        let messageClass = (msg.admin_id || msg.user_id === {{ Auth::user()->id }}) ? 'user' : 'admin';
                        messageDiv.classList.add('message', messageClass);
                        messageDiv.innerHTML = `${msg.message} <span class="message-time">${new Date(msg.created_at).toLocaleTimeString()}</span>`;
                        chatWindow.appendChild(messageDiv);
                        messageDiv.addEventListener('click', () => {
                            const timeSpan = messageDiv.querySelector('.message-time');
                            timeSpan.style.display = timeSpan.style.display === 'block' ? 'none' : 'block';
                        });
                    });
                    sendMessageForm.action = url + '/send-message';
                    chatWindowModal.style.display = 'block';
                    chatWindow.scrollTop = chatWindow.scrollHeight;

                    // Nasłuchiwanie eventów dla tego chatu
                    window.Echo.private(`chat.${currentChatId}`)
                        .listen('MessageSent', (e) => {
                            console.log(e.message);
                            let messageDiv = document.createElement('div');
                            let messageClass = (e.message.admin_id || e.message.user_id === {{ Auth::user()->id }}) ? 'user' : 'admin';
                            messageDiv.classList.add('message', messageClass);
                            messageDiv.innerHTML = `${e.message.message} <span class="message-time">${new Date(e.message.created_at).toLocaleTimeString()}</span>`;
                            chatWindow.appendChild(messageDiv);
                            chatWindow.scrollTop = chatWindow.scrollHeight;
                        });
                })
                .catch(error => {
                    console.error('Error fetching chat messages:', error);
                });
        });
    });

    if (manageButton) {
        manageButton.addEventListener('click', function() {
            if (!currentChatId) {
                console.error('Chat ID is not set');
                return;
            }
            console.log('Manage Button Clicked. Current Chat ID:', currentChatId); // Debugging line
            document.getElementById('manageForm').action = `/chat/${currentChatId}/manage`;
            manageModal.style.display = 'block';
        });
    }

    takeChatButton.addEventListener('click', function() {
        console.log('Take Chat Button Clicked. Current Chat ID:', currentChatId); // Debugging line
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
                alert('Chat has been taken successfully.');
                manageModal.style.display = 'none';
                chatWindowModal.style.display = 'none';
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error taking chat:', error);
        });
    });

    document.querySelectorAll('.close-custom').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    document.getElementById('manageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Submit Manage Form. Current Chat ID:', currentChatId); // Debugging line
        if (!currentChatId) {
            console.error('Chat ID is not set');
            return;
        }
        let status = document.getElementById('status').value;
        fetch(this.action, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status, is_taken: true, admin_id: {{ Auth::user()->id }} })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Chat status has been successfully updated.');
                manageModal.style.display = 'none';
                chatWindowModal.style.display = 'none';
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error updating chat status:', error);
        });
    });

    sendMessageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Submit Message Form. Current Chat ID:', currentChatId); // Debugging line
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
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
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
