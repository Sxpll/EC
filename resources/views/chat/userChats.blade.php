@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="elo">Your Threads</h1>
    <button id="newThreadButton" class="new-chat-button">New Chat</button>
    <select id="chatStatusFilter" class="form-control mt-3" style="max-width: 200px;">
        <option value="open" selected>Open</option>
        <option value="completed">Completed</option>
    </select>
    <ul class="list-group mt-3" id="chatList">
        @foreach($chats as $chat)
            @if($chat->status == 'open' || $chat->status == 'in progress')
                <li class="list-group-item chat-item" data-status="open">
                    <a href="#" class="chat-link" data-chat-id="{{ $chat->id }}">
                        <div class="chat-title">{{ $chat->title }}</div>
                        <div class="chat-time">{{ $chat->created_at->format('Y-m-d H:i') }}</div>
                    </a>
                </li>
            @elseif($chat->status == 'completed')
                <li class="list-group-item chat-item d-none" data-status="completed">
                    <a href="#" class="chat-link" data-chat-id="{{ $chat->id }}">
                        <div class="chat-title">{{ $chat->title }}</div>
                        <div class="chat-time">{{ $chat->created_at->format('Y-m-d H:i') }}</div>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</div>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newThreadButton = document.getElementById('newThreadButton');
    const createChatForm = document.getElementById('createChatForm');
    const newChatModal = document.getElementById('newChatModal');
    const chatWindowModal = document.getElementById('chatWindowModal');
    const chatWindow = document.getElementById('chat-window');
    const chatTitle = document.getElementById('chatTitle');
    const messageTextarea = document.querySelector('#sendMessageForm textarea');
    const chatStatusFilter = document.getElementById('chatStatusFilter');
    const chatListItems = document.querySelectorAll('.chat-item');

    newThreadButton.addEventListener('click', function() {
        newChatModal.style.display = 'block';
    });

    chatStatusFilter.addEventListener('change', function() {
        const selectedStatus = this.value;
        chatListItems.forEach(item => {
            if (item.getAttribute('data-status') === selectedStatus) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });
    });

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
                        messageDiv.classList.add('message', messageClass);
                        messageDiv.innerHTML = `${msg.message}`;
                        let messageTime = document.createElement('div');
                        messageTime.classList.add('message-time');
                        messageTime.textContent = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        messageTime.style.display = 'none';
                        messageDiv.appendChild(messageTime);
                        messageDiv.addEventListener('click', () => {
                            messageTime.style.display = messageTime.style.display === 'block' ? 'none' : 'block';
                        });
                        chatWindow.appendChild(messageDiv);
                    });
                    document.getElementById('sendMessageForm').action = url + '/send-message';
                    chatWindowModal.style.display = 'block';
                    chatTitle.textContent = messages.length > 0 ? messages[0].chat.title : 'Chat';
                    scrollToBottom(chatWindow);
                });
        });
    });

    createChatForm.addEventListener('submit', function(e) {
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
            console.log(data); // Logowanie danych
            if (data.success) {
                let chatList = document.getElementById('chatList');
                let newChatItem = document.createElement('li');
                newChatItem.classList.add('list-group-item', 'chat-item');
                newChatItem.setAttribute('data-status', 'open');
                newChatItem.innerHTML = `
                    <a href="#" class="chat-link" data-chat-id="${data.chat.id}">
                        <div class="chat-title">${data.chat.title}</div>
                        <div class="chat-time">${data.chat.created_at}</div>
                    </a>
                `;
                chatList.appendChild(newChatItem);
                newChatModal.style.display = 'none';
                document.getElementById('title').value = '';

                newChatItem.querySelector('.chat-link').addEventListener('click', function(e) {
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
                                messageDiv.classList.add('message', messageClass);
                                messageDiv.innerHTML = `${msg.message}`;
                                let messageTime = document.createElement('div');
                                messageTime.classList.add('message-time');
                                messageTime.textContent = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                                messageTime.style.display = 'none';
                                messageDiv.appendChild(messageTime);
                                messageDiv.addEventListener('click', () => {
                                    messageTime.style.display = messageTime.style.display === 'block' ? 'none' : 'block';
                                });
                                chatWindow.appendChild(messageDiv);
                            });
                            document.getElementById('sendMessageForm').action = url + '/send-message';
                            chatWindowModal.style.display = 'block';
                            scrollToBottom(chatWindow);
                        });
                });
            }
        }).catch(error => console.error('Error:', error)); // Logowanie błędów
    });

    document.querySelectorAll('.close-custom').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
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
                newMessage.classList.add('message', 'user');
                newMessage.innerHTML = `${message}`;
                let messageTime = document.createElement('div');
                messageTime.classList.add('message-time');
                messageTime.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                messageTime.style.display = 'none';
                newMessage.appendChild(messageTime);
                newMessage.addEventListener('click', () => {
                    messageTime.style.display = messageTime.style.display === 'block' ? 'none' : 'block';
                });
                chatWindow.appendChild(newMessage);
                this.message.value = '';
                scrollToBottom(chatWindow);
            }
        });
    });

    messageTextarea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('sendMessageForm').dispatchEvent(new Event('submit'));
        }
    });

    function scrollToBottom(element) {
        element.scrollTop = element.scrollHeight;
    }
});
</script>
@endsection
