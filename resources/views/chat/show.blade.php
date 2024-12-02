@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Chat: {{ $chat->title }}</h1>

    <div class="chat-messages">
        @foreach ($chat->messages as $message)
        <div>
            <strong>{{ $message->user->name ?? 'Admin' }}:</strong>
            <p>{{ $message->message }}</p>
        </div>
        @endforeach
    </div>

    <form action="{{ route('chat.sendMessage', ['id' => $chat->id]) }}" method="POST">
        @csrf
        <div class="form-group">
            <textarea name="message" class="form-control" rows="3" placeholder="Write a message..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>

<script>
    document.getElementById('sendMessageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        let message = this.message.value;
        fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    message: message
                })
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    let messagesDiv = document.getElementById('messages');
                    let newMessage = document.createElement('div');
                    newMessage.innerHTML = '<strong>' + (data.is_from_user ? 'User' : 'Admin') + ':</strong> ' + message;
                    messagesDiv.appendChild(newMessage);
                    this.message.value = '';
                }
            });
    });
</script>
@endsection
