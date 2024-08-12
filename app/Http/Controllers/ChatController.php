<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

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
    \Log::info('Start sendMessage function for chat ID: ' . $id);

    try {
        $chat = Chat::findOrFail($id);
        \Log::info('Chat found: ' . $chat->id);

        if ($chat->status === 'completed') {
            \Log::warning('Cannot send messages to a completed chat.');
            return response()->json(['success' => false, 'message' => 'Cannot send messages to a completed chat.']);
        }

        $message = new Message();
        $message->chat_id = $chat->id;
        $message->message = $request->message;
        $message->admin_id = Auth::user()->role === 'admin' ? Auth::id() : null;
        $message->save();
        \Log::info('Message saved for chat: ' . $chat->id);

        // Wysyłanie powiadomień
        if (!$chat->admin_id) {
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'chat_id' => $chat->id,
                    'user_id' => $admin->id,
                    'message' => 'Nowa wiadomość w czacie: ' . $chat->title,
                    'read' => false,
                ]);
                \Log::info('Notification sent to admin ID: ' . $admin->id);
            }
        } else {
            Notification::create([
                'chat_id' => $chat->id,
                'user_id' => $chat->admin_id,
                'message' => 'Nowa wiadomość w czacie: ' . $chat->title,
                'read' => false,
            ]);
            \Log::info('Notification sent to assigned admin ID: ' . $chat->admin_id);
        }

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        \Log::error('Error in sendMessage: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Internal Server Error'], 500);
    }
}

    



    public function takeChat($id)
{
    $chat = Chat::findOrFail($id);
    if (Auth::user()->role === 'admin') {
        $chat->admin_id = Auth::id();
        $chat->is_taken = true;
        $chat->save();

        // Usunięcie poprzednich powiadomień, które zostały wysłane do wszystkich adminów
        Notification::where('chat_id', $chat->id)
                    ->where('user_id', '!=', Auth::id())
                    ->delete();

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
    $chat->is_taken = $request->input('is_taken', false);
    $chat->admin_id = $request->input('admin_id', null);
    $chat->save();

    return redirect()->back()->with('success', 'Chat status has been successfully updated.');
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

   
    
    
    
}
