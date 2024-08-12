<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
                                     ->where('read', false)
                                     ->orderBy('created_at', 'desc')
                                     ->get();

        return response()->json($notifications);
    }

    public function markAllAsRead(Request $request)
    {
        $chatId = $request->input('chat_id');
        
        Notification::where('chat_id', $chatId)
                    ->where('user_id', Auth::id())
                    ->update(['read' => true]);
    
        return response()->json(['success' => true]);
    }
    

}
