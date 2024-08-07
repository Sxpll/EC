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
            <button id="manageButton" class="btn btn-view">Manage</button>
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
                    <button type="button" id="takeChatButton" class="btn btn-view">Take Chat</button>
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
    let currentChatId;

    document.querySelectorAll('.btn-view').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            currentChatId = this.getAttribute('data-chat-id');
            let url = `/chat/${currentChatId}`;
            fetch(url)
                .then(response => response.json())
                .then(messages => {
                    chatWindow.innerHTML = '';
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
                });
        });
    });

    sendMessageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        let message = messageInput.value;
        fetch(this.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ message: message })
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                let newMessage = document.createElement('div');
                newMessage.classList.add('message', 'user');
                newMessage.innerHTML = `${message} <span class="message-time">${new Date().toLocaleTimeString()}</span>`;
                chatWindow.appendChild(newMessage);
                messageInput.value = '';
                chatWindow.scrollTop = chatWindow.scrollHeight;
            }
        });
    });

    sendMessageForm.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessageForm.dispatchEvent(new Event('submit'));
        }
    });

    document.querySelector('#manageButton').addEventListener('click', function() {
        document.getElementById('manageForm').action = `/chat/${currentChatId}/manage`;
        manageModal.style.display = 'block';
    });

    document.querySelector('#takeChatButton').addEventListener('click', function() {
        fetch(`/chat/${currentChatId}/take`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Chat has been taken successfully.');
                manageModal.style.display = 'none';
                chatWindowModal.style.display = 'none';
                location.reload();
            }
        });
    });

    document.querySelectorAll('.close-custom').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    document.getElementById('manageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        let status = document.getElementById('status').value;
        fetch(this.action, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status, is_taken: true, admin_id: {{ Auth::user()->id }} })
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                manageModal.style.display = 'none';
                chatWindowModal.style.display = 'none';
                location.reload();
            }
        });
    });
});
</script>   
@endsection
