<!-- resources/views/chat/userChats.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <h2>Chats</h2>
            <ul class="list-group">
                @foreach($chats as $chat)
                    <li class="list-group-item">
                        <a href="{{ route('chat.show', $chat->id) }}">
                            {{ $chat->user->name }} ({{ $chat->created_at->diffForHumans() }})
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-8">
            <h2>Chat Window</h2>
            <div id="chat-window"></div>
        </div>
    </div>
</div>
@endsection
