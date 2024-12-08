<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use App\Events\MessageSent;



class ChatController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        if (Auth::user()->role === 'admin') {
            $search = $request->get('search');
            $chats = Chat::with(['user:id,name,lastname', 'messages' => function ($query) {
                $query->select('id', 'chat_id', 'is_read', 'admin_id');
            }])
                ->when($search, function ($query, $search) {
                    return $query->where('title', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        });
                })
                ->orderBy('created_at', 'desc')
                ->get();

            if ($request->ajax()) {
                return response()->json($chats);
            }

            return view('chat.index', compact('chats'));
        } else {
            return redirect()->route('chat.userChats');
        }
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
        try {
            $chat = Chat::with(['messages.user', 'admin'])->findOrFail($id);

            if (Auth::user()->role !== 'admin' && $chat->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }

            if (Auth::user()->role === 'admin') {
                Message::where('chat_id', $id)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            }

            return response()->json([
                'messages' => $chat->messages,
                'admin' => $chat->admin,
                'is_admin' => Auth::user()->role === 'admin',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Chat not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching chat: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred.'], 500);
        }
    }



    public function __construct()
    {
        $this->middleware('auth');
    }



    public function sendMessage(Request $request, $chatId)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $chat = Chat::findOrFail($chatId);

        $message = new Message([
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);

        $message->save();

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['success' => true, 'message' => $message]);
    }





    public function takeChat($id)
    {
        $chat = Chat::findOrFail($id);
        if (Auth::user()->role === 'admin') {
            $chat->admin_id = Auth::id();
            $chat->is_taken = true;
            $chat->save();

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
            $request->validate([
                'title' => 'required|string|max:255',
            ]);

            // Tworzenie nowego czatu
            $chat = new Chat();
            $chat->user_id = Auth::id();
            $chat->title = $request->title;
            $chat->save();

            // Tworzenie domyślnej wiadomości w czacie
            $message = new Message();
            $message->chat_id = $chat->id;
            $message->message = 'Hello!';
            $message->user_id = Auth::id();
            $message->save();

            // Przekierowanie do widoku czatu
            return redirect()->route('chat.show', ['id' => $chat->id]);
        } catch (\Exception $e) {
            Log::error('Chat creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create chat.');
        }
    }




    public function updateChatStatus(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);
        $chat->status = $request->status;
        if ($request->has('is_taken')) {
            $chat->is_taken = 1;
            $chat->admin_id = Auth::user()->id;
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

        return response()->json(['success' => true]);
    }


    public function filterChats(Request $request)
    {
        $status = $request->get('status');
        $user_id = Auth::id();

        Log::info('FilterChats invoked', ['status' => $status, 'user_id' => $user_id]);

        $chatsQuery = Chat::where('user_id', $user_id);

        if ($status === 'open') {
            $chatsQuery->whereIn('status', ['open', 'ongoing']);
        } elseif ($status === 'completed') {
            $chatsQuery->where('status', 'completed');
        }

        $chats = $chatsQuery->orderBy('created_at', 'desc')->get();

        Log::info('Chats retrieved', ['count' => $chats->count()]);

        return response()->json(['chats' => $chats]);
    }


    public function getMessages($id)
    {
        $chat = Chat::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $messages = Message::where('chat_id', $id)
            ->orderBy('created_at', 'asc')
            ->get(['id', 'message', 'created_at', 'user_id'])
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'created_at' => $message->created_at->toDateTimeString(),
                    'user' => $message->user ? [
                        'id' => $message->user->id,
                        'name' => $message->user->name,
                    ] : null,
                ];
            });

        return response()->json($messages);
    }
}
