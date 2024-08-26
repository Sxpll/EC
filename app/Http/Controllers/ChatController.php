<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;


class ChatController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        Log::info('Search request received', ['search' => $search]);

        $chats = Chat::with(['user' => function ($query) {
            $query->select('id', 'name', 'lastname'); // Upewnij się, że wybierasz zarówno imię, jak i nazwisko
        }])
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        Log::info('Chats found', ['count' => count($chats)]);

        if ($request->ajax()) {
            return response()->json($chats);
        }

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

        // Upewnij się, że tylko użytkownik powiązany z czatem lub admin ma dostęp
        if (Auth::user()->role !== 'admin' && $chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        return response()->json(['messages' => $chat->messages, 'admin' => $chat->admin]);
    }


    public function sendMessage(Request $request, $id)
    {
        Log::info('Start sendMessage function for chat ID: ' . $id);

        // Walidacja żądania
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        try {
            // Znajdź czat, sprawdź, czy istnieje
            $chat = Chat::findOrFail($id);
            Log::info('Chat found: ' . $chat->id);

            // Sprawdź, czy czat nie jest zakończony
            if ($chat->status === 'completed') {
                Log::warning('Cannot send messages to a completed chat.');
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot send messages to a completed chat.'
                ], 400);
            }

            // Utwórz nową wiadomość
            $message = new Message();
            $message->chat_id = $chat->id;
            $message->message = $request->message;
            $message->admin_id = Auth::user()->role === 'admin' ? Auth::id() : null;
            $message->save();
            Log::info('Message saved for chat: ' . $chat->id);

            // Wysyłanie powiadomień tylko dla użytkowników, gdy wiadomość pochodzi od admina
            if (!$chat->admin_id || $message->admin_id !== Auth::id()) {
                $admins = \App\Models\User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    // Sprawdzenie, aby nie wysłać powiadomienia do samego siebie
                    if ($admin->id !== Auth::id()) {
                        Notification::create([
                            'chat_id' => $chat->id,
                            'user_id' => $admin->id,
                            'message' => 'Nowa wiadomość w czacie: ' . $chat->title,
                            'read' => false,
                        ]);
                        Log::info('Notification sent to admin ID: ' . $admin->id);
                    }
                }
            } else {
                // Powiadomienie dla przypisanego admina
                Notification::create([
                    'chat_id' => $chat->id,
                    'user_id' => $chat->admin_id,
                    'message' => 'Nowa wiadomość w czacie: ' . $chat->title,
                    'read' => false,
                ]);
                Log::info('Notification sent to assigned admin ID: ' . $chat->admin_id);
            }

            // Zwróć pomyślną odpowiedź
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            Log::error('Error in sendMessage: ' . $e->getMessage());

            // Zwróć odpowiedź z kodem błędu 500
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error'
            ], 500);
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
            Log::error('Chat creation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Chat creation failed'], 500);
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

        return redirect()->back()->with('success', 'Chat status has been successfully updated.');
    }

    public function filterChats(Request $request)
    {
        $status = $request->get('status');
        $user_id = Auth::id();

        $chats = Chat::where('user_id', $user_id)
            ->where(function ($query) use ($status) {
                if ($status === 'open') {
                    $query->whereIn('status', ['open', 'ongoing']);
                } elseif ($status === 'completed') {
                    $query->where('status', 'completed');
                }
            })
            ->orderBy('created_at', 'desc')  // Sortowanie od najnowszych do najstarszych
            ->get();

        return response()->json($chats);
    }


    public function getMessages($id)
    {
        $chat = Chat::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $messages = Message::where('chat_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }
}
