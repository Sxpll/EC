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
        $chats = Chat::with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('chat.index', compact('chats'));
    }

    public function userChats()
    {
        $chats = Chat::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        return view('chat.userChats', compact('chats'));
    }

    public function show($id)
{
    $chat = Chat::with('messages', 'admin')->findOrFail($id);
    return response()->json(['messages' => $chat->messages, 'admin' => $chat->admin]);
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
        if (Auth::user()->role === 'admin') {
            $chat->admin_id = Auth::id();
            $chat->is_taken = true;
            $chat->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function createChat(Request $request)
    {
        try {
            $chat = new Chat();
            $chat->user_id = Auth::id();
            $chat->title = $request->title;
            $chat->save();

            $message = new Message();
            $message->chat_id = $chat->id;
            $message->message = 'Hello!';
            $message->save();

            return response()->json(['success' => true, 'chat' => $chat]);
        } catch (\Exception $e) {
            \Log::error('Chat creation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Chat creation failed'], 500);
        }
    }

    public function updateChatStatus(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);
        $chat->status = $request->status;
        if ($request->has('is_taken')) {
            $chat->is_taken = 1;
            $chat->admin_id = auth()->user()->id;
        } else {
            $chat->is_taken = 0;
            $chat->admin_id = null;
        }
        $chat->save();

        return response()->json(['success' => true]);
    }

    public function manageChat(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);
        $chat->status = $request->status;
        $chat->is_taken = $request->is_taken;
        $chat->admin_id = $request->admin_id;
        $chat->save();

        return response()->json(['success' => true]);
    }

    public function filterChats(Request $request)
    {
        $status = $request->get('status');
        $user_id = Auth::id();

        $chats = Chat::where('user_id', $user_id)
            ->where(function($query) use ($status) {
                if ($status === 'open') {
                    $query->whereIn('status', ['open', 'in progress']);
                } elseif ($status === 'completed') {
                    $query->where('status', 'completed');
                }
            })
            ->get();

        return response()->json($chats);
    }

    public function getMessages($id)
{
    $messages = Message::where('chat_id', $id)
                       ->orderBy('created_at', 'asc')
                       ->get();

    return response()->json($messages);
}


public function checkNewMessages()
{
    $adminId = Auth::id();
    $chats = Chat::where('admin_id', $adminId)->with('messages')->get();

    $newMessages = [];
    foreach ($chats as $chat) {
        $latestMessage = $chat->messages()->latest()->first();
        if ($latestMessage && $latestMessage->created_at->gt($chat->updated_at)) {
            $newMessages[] = ['id' => $chat->id, 'title' => $chat->title];
        }
    }

    return response()->json(['newMessages' => $newMessages]);
}
    
}
