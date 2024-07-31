@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Chat with {{ $chat->user->name }}</h1>
    <div id="messages">
        @foreach($chat->messages as $message)
            <div>
                <strong>{{ $message->sender->name }}:</strong> {{ $message->message }}
            </div>
        @endforeach
    </div>
    <form action="{{ route('chat.sendMessage', $chat->id) }}" method="POST">
        @csrf
        <textarea name="message" rows="3" required></textarea>
        <button type="submit">Send</button>
    </form>
</div>
@endsection
