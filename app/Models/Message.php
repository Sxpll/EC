<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';
    protected $fillable = ['chat_id', 'message', 'admin_id', 'user_id', 'is_read']; // Dodano 'user_id' i 'is_read'

    // Relacja do czatu, do którego należy wiadomość
    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    // Relacja do administratora, który wysłał wiadomość
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Relacja do użytkownika, który wysłał wiadomość (może być administratorem lub zwykłym użytkownikiem)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
