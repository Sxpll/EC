<?php
namespace App\Http\Controllers;

use App\Models\Chat;
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
        return view('chat.show', compact('chat'));
    }

    public function sendMessage(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);
        $message = new Message();
        $message->chat_id = $chat->id;
        $message->message = $request->message;
        $message->is_from_user = Auth::user()->role === 'user';
        $message->save();

        return response()->json(['success' => true]);
    }

    public function takeChat($id)
    {
        $chat = Chat::findOrFail($id);
        if (Auth::user()->role === 'admin' && Auth::user()->is_hr) {
            $chat->admin_id = Auth::id();
            $chat->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function createChat(Request $request)
    {
        $existingChat = Chat::where('user_id', Auth::id())->where('admin_id', null)->first();
        if ($existingChat) {
            return redirect()->back()->with('error', 'You already have an open chat.');
        }

        $chat = new Chat();
        $chat->user_id = Auth::id();
        $chat->save();

        $message = new Message();
        $message->chat_id = $chat->id;
        $message->message = $request->message;
        $message->is_from_user = true;
        $message->save();

        return redirect()->route('chat.userChats');
    }
}
