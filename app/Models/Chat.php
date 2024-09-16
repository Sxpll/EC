<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'user_chats';
    protected $fillable = ['user_id', 'admin_id', 'status', 'is_taken', 'title'];

    // Relacja do wiadomości powiązanych z czatem
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Relacja do użytkownika, który utworzył czat
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relacja do administratora czatu
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
