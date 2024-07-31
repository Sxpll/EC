@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Your Chats</h1>
    <button id="newThreadButton" class="btn btn-primary">New Chat</button>
    <ul class="list-group mt-3" id="chatList">
        @foreach($chats as $chat)
            <li class="list-group-item chat-item">
                <a href="#" class="chat-link" data-chat-id="{{ $chat->id }}">
                    <div class="chat-title">{{ $chat->title }}</div>
                    <div class="chat-time">{{ $chat->created_at->format('Y-m-d H:i') }}</div>
                </a>
            </li>
        @endforeach
    </ul>
</div>

<div id="newChatModal" class="modal" tabindex="-1" role="dialog" style="display:none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">New Chat</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
          <input type="hidden" name="message" value="Initial message">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Start Chat</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div id="chatWindowModal" class="modal" tabindex="-1" role="dialog" style="display:none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Chat</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="chat-window" style="height: 400px; overflow-y: scroll;">
        <!-- Messages will be loaded here -->
      </div>
      <div class="modal-footer">
        <form id="sendMessageForm" action="" method="POST">
            @csrf
            <textarea name="message" rows="3" class="form-control" required></textarea>
            <button type="submit" class="btn btn-primary mt-2">Send</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('newThreadButton').addEventListener('click', function() {
        document.getElementById('newChatModal').style.display = 'block';
    });

    document.querySelectorAll('.chat-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            let chatId = this.getAttribute('data-chat-id');
            let url = `/chat/${chatId}`;
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
                    document.getElementById('chatWindowModal').style.display = 'block';
                });
        });
    });

    document.getElementById('createChatForm').addEventListener('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                let chatList = document.getElementById('chatList');
                let newChatItem = document.createElement('li');
                newChatItem.classList.add('list-group-item', 'chat-item');
                newChatItem.innerHTML = `
                    <a href="#" class="chat-link" data-chat-id="${data.chat.id}">
                        <div class="chat-title">${data.chat.title}</div>
                        <div class="chat-time">${data.chat.created_at}</div>
                    </a>
                `;
                chatList.appendChild(newChatItem);
                document.getElementById('newChatModal').style.display = 'none';
                document.getElementById('title').value = '';
            }
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
                newMessage.innerHTML = '<strong>User:</strong> ' + message;
                chatWindow.appendChild(newMessage);
                this.message.value = '';
            }
        });
    });
});
</script>
@endsection
