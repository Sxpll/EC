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
    <ul class="list-group mt-3" id="chatList">
        @foreach($chats as $chat)
            <li class="list-group-item chat-item" data-status="{{ $chat->status }}">
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
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="{{ mix('js/app.js') }}"></script>


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
    const chatList = document.getElementById('chatList');
    let currentChatId = null;

    newThreadButton.addEventListener('click', function() {
        newChatModal.style.display = 'block';
    });

    chatStatusFilter.addEventListener('change', function() {
        const selectedStatus = this.value;
        axios.get(`/chat/filter`, { params: { status: selectedStatus } })
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
                    chatItem.querySelector('.chat-link').addEventListener('click', function(e) {
                        e.preventDefault();
                        loadChatMessages(chat.id);
                    });
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while filtering chats. Please try again.');
            });
    });

    document.querySelectorAll('.chat-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            let chatId = this.getAttribute('data-chat-id');
            loadChatMessages(chatId);
        });
    });

    createChatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        axios.post(this.action, formData)
            .then(response => {
                console.log(response.data);
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

                    newChatItem.querySelector('.chat-link').addEventListener('click', function(e) {
                        e.preventDefault();
                        let chatId = this.getAttribute('data-chat-id');
                        loadChatMessages(chatId);
                    });
                }
            })
            .catch(error => console.error('Error:', error));
    });

    document.querySelectorAll('.close-custom').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    document.getElementById('sendMessageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        let message = this.message.value;
        axios.post(this.action, { message: message })
            .then(response => {
                if (response.data.success) {
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
            })
            .catch(error => console.error('Error:', error));
    });

    messageTextarea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('sendMessageForm').dispatchEvent(new Event('submit'));
        }
    });

    function loadChatMessages(chatId) {
        let url = `/chat/${chatId}`;
        axios.get(url)
            .then(response => {
                chatWindow.innerHTML = '';
                response.data.forEach(msg => {
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
                if (response.data.length > 0 && response.data[0].chat) {
                    chatTitle.textContent = response.data[0].chat.title;
                } else {
                    chatTitle.textContent = 'Chat';
                }
                scrollToBottom(chatWindow);

                
                window.Echo.private(`chat.${chatId}`)
                    .listen('MessageSent', (e) => {
                        console.log(e.message);
                        let messageDiv = document.createElement('div');
                        let messageClass = (e.message.admin_id || e.message.user_id === {{ Auth::user()->id }}) ? 'user' : 'admin';
                        messageDiv.classList.add('message', messageClass);
                        messageDiv.innerHTML = `${e.message.message} <span class="message-time">${new Date(e.message.created_at).toLocaleTimeString()}</span>`;
                        chatWindow.appendChild(messageDiv);
                        chatWindow.scrollTop = chatWindow.scrollHeight;
                    });
            }).catch(error => console.error('Error:', error));
    }

    function scrollToBottom(element) {
        element.scrollTop = element.scrollHeight;
    }
});

</script>
@endsection
