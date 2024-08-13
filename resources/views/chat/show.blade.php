@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Chat with {{ $chat->user->name }}</h1>
    <div id="messages" style="height: 300px; border: 1px solid #ccc; padding: 10px; overflow-y: scroll;">
        @foreach($chat->messages as $message)
            <div>
                <strong>{{ $message->is_from_user ? 'User' : 'Admin' }}:</strong> {{ $message->message }}
            </div>
        @endforeach
    </div>
    <form id="sendMessageForm" action="{{ route('chat.sendMessage', $chat->id) }}" method="POST">
        @csrf
        <textarea name="message" rows="3" required></textarea>
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
        body: JSON.stringify({ message: message })
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
