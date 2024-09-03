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
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/home')->with('error', 'Unauthorized access');
        }

        $search = $request->get('search');
        Log::info('Search request received', ['search' => $search]);

        $chats = Chat::with(['user:id,name,lastname', 'messages' => function ($query) {
            $query->select('id', 'chat_id', 'is_read', 'admin_id'); // Dodano 'admin_id'
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

        // Logowanie szczegółowych danych
        foreach ($chats as $chat) {
            Log::info('Chat details', ['id' => $chat->id, 'title' => $chat->title, 'messages' => $chat->messages->toArray()]);
        }

        // Zwraca dane w formacie JSON, jeśli jest to żądanie AJAX
        if ($request->ajax()) {
            return response()->json($chats);
        }

        // Dla zwykłych żądań HTTP zwraca widok
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

        // Sprawdź, czy użytkownik ma uprawnienia do dostępu do czatu
        if (Auth::user()->role !== 'admin' && $chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        // Oznacz wiadomości jako przeczytane, jeśli admin je przegląda
        if (Auth::user()->role === 'admin') {
            Message::where('chat_id', $id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
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
            $message->is_read = false; // Oznacz nową wiadomość jako nieprzeczytaną
            $message->save();
            Log::info('Message saved for chat: ' . $chat->id);

            // Sprawdzenie, czy wiadomość pochodzi od admina
            if ($message->admin_id) {
                // Jeśli admin wysłał wiadomość, sprawdź, czy to admin przypisany do czatu
                if ($chat->admin_id && $chat->admin_id !== $message->admin_id) {
                    // Powiadom przypisanego admina, że nowa wiadomość została wysłana
                    Notification::create([
                        'chat_id' => $chat->id,
                        'user_id' => $chat->admin_id,
                        'message' => 'Nowa wiadomość w czacie: ' . $chat->title,
                        'read' => false,
                    ]);
                    Log::info('Notification sent to assigned admin ID: ' . $chat->admin_id);
                }
            } else {
                // Wiadomość pochodzi od użytkownika, wyślij powiadomienie do przypisanego admina
                if ($chat->admin_id) {
                    Notification::create([
                        'chat_id' => $chat->id,
                        'user_id' => $chat->admin_id,
                        'message' => 'Nowa wiadomość w czacie: ' . $chat->title,
                        'read' => false,
                    ]);
                    Log::info('Notification sent to assigned admin ID: ' . $chat->admin_id);
                }
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
            ->get();

        return response()->json($messages);
    }
}
