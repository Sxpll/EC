@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-4 user-list">
            <input type="text" class="search-bar form-control" placeholder="Search...">
            <ul class="list-group mt-3" id="chatList">
                @foreach($chats as $chat)
                    <li class="list-group-item chat-item">
                        <a href="#" class="chat-link" data-chat-id="{{ $chat->id }}">
                            <div class="chat-title">{{ $chat->user->name }} {{ $chat->user->surname }}</div>
                            <div class="chat-time">{{ $chat->created_at->format('Y-m-d H:i') }}</div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-8 chat-window">
            <div class="chat-header">
                <h5 class="chat-title">Chat</h5>
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="chat-messages" id="chat-window"></div>
            <form id="sendMessageForm" action="" method="POST" class="chat-input">
                @csrf
                <textarea name="message" rows="1" class="form-control" placeholder="Type your message..." required></textarea>
                <button type="submit" class="btn btn-primary mt-2">Send</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatWindowModal = document.getElementById('chatWindowModal');
    const chatWindow = document.getElementById('chat-window');

    document.querySelectorAll('.chat-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            let chatId = this.getAttribute('data-chat-id');
            let url = `/chat/${chatId}`;
            fetch(url)
                .then(response => response.json())
                .then(messages => {
                    chatWindow.innerHTML = '';
                    messages.forEach(msg => {
                        let messageDiv = document.createElement('div');
                        let messageClass = msg.admin_id ? 'admin' : 'user';
                        let messageSender = msg.admin_id ? 'Admin' : '{{ $chat->user->name }} {{ $chat->user->surname }}';
                        messageDiv.classList.add('message', messageClass);
                        messageDiv.innerHTML = `<strong>${messageSender}:</strong> ${msg.message}`;
                        chatWindow.appendChild(messageDiv);
                    });
                    document.getElementById('sendMessageForm').action = url + '/send-message';
                    chatWindowModal.style.display = 'block';
                });
        });
    });

    document.getElementById('sendMessageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        let message = this.message.value;
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
                newMessage.classList.add('message', 'admin');
                newMessage.innerHTML = `<strong>Admin:</strong> ${message}`;
                chatWindow.appendChild(newMessage);
                this.message.value = '';
            }
        });
    });
});
</script>
@endsection
