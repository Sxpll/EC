@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <h2>Chats</h2>
            <ul class="list-group" id="chatList">
                @foreach($chats as $chat)
                    <li class="list-group-item">
                        <a href="{{ route('chat.show', $chat->id) }}" class="chat-link">
                            {{ $chat->user->name }} ({{ $chat->created_at->diffForHumans() }})
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-8">
            <h2>Chat Window</h2>
            <div id="chat-window" style="height: 400px; border: 1px solid #ccc; padding: 10px; overflow-y: scroll;"></div>
            <form id="sendMessageForm" action="" method="POST" style="margin-top: 20px;">
                @csrf
                <textarea name="message" rows="3" class="form-control" required></textarea>
                <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Send</button>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.chat-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        let url = this.href;
        fetch(url)
            .then(response => response.json())
            .then(messages => {
                let chatWindow = document.getElementById('chat-window');
                chatWindow.innerHTML = '';
                messages.forEach(msg => {
                    let messageDiv = document.createElement('div');
                    messageDiv.innerHTML = '<strong>' + (msg.admin_id ? 'Admin' : 'User') + ':</strong> ' + msg.message;
                    chatWindow.appendChild(messageDiv);
                });

                document.getElementById('sendMessageForm').action = url + '/send-message';
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
            let chatWindow = document.getElementById('chat-window');
            let newMessage = document.createElement('div');
            newMessage.innerHTML = '<strong>Admin:</strong> ' + message;
            chatWindow.appendChild(newMessage);
            this.message.value = '';
        }
    });
});
</script>
@endsection
