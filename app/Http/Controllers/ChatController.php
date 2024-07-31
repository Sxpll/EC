<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $chats = Chat::where('admin_id', null)->orWhere('admin_id', Auth::id())->get();
        return view('chat.index', compact('chats'));
    }

    public function userChats()
    {
        $chats = Chat::where('user_id', Auth::id())->get();
        return view('chat.userChats', compact('chats'));
    }

    public function show($id)
    {
        $chat = Chat::findOrFail($id);
        return response()->json($chat->messages);
    }

    public function sendMessage(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);
        $message = new Message();
        $message->chat_id = $chat->id;
        $message->message = $request->message;
        $message->admin_id = Auth::user()->role === 'admin' ? Auth::id() : null;
        $message->save();

        return response()->json(['success' => true]);
    }

    public function takeChat($id)
    {
        $chat = Chat::findOrFail($id);
        if (Auth::user()->role === 'admin' && Auth::user()->is_hr) {
            $chat->admin_id = Auth::id();
            $chat->is_taken = true;
            $chat->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function createChat(Request $request)
    {
        $existingChat = Chat::where('user_id', Auth::id())->where('is_taken', false)->first();
        if ($existingChat) {
            return response()->json(['error' => 'You already have an open chat.']);
        }

        $chat = new Chat();
        $chat->user_id = Auth::id();
        $chat->title = $request->title;
        $chat->save();

        $message = new Message();
        $message->chat_id = $chat->id;
        $message->message = 'Initial message';
        $message->save();

        return response()->json(['success' => true, 'chat' => $chat]);
    }
}
