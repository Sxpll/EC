@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Your Chats</h1>
    <button id="newThreadButton" class="btn btn-primary">New Chat</button>
    <ul class="list-group mt-3" id="chatList">
        @foreach($chats as $chat)
            <li class="list-group-item">
                <a href="{{ route('chat.show', $chat->id) }}" class="chat-link">
                    {{ $chat->created_at->diffForHumans() }}
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
        <button type="button" class="close" onclick="closeModal()">&times;</button>
      </div>
      <form action="{{ route('chat.createChat') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label for="message">Message</label>
            <textarea class="form-control" name="message" id="message" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Start Chat</button>
          <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
        </div>
      </form>
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
                newMessage.innerHTML = '<strong>User:</strong> ' + message;
                chatWindow.appendChild(newMessage);
                this.message.value = '';
            }
        });
    });
});

function closeModal() {
    document.getElementById('newChatModal').style.display = 'none';
}
</script>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-dialog {
    margin: 15% auto;
    padding: 20px;
    width: 80%;
}

.modal-content {
    background-color: #fefefe;
    border: 1px solid #888;
    border-radius: 5px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
}

.modal-header, .modal-body, .modal-footer {
    padding: 10px 20px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover, .close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>
@endsection
