<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    
    protected $table = 'chat_messages';
    protected $fillable = ['chat_id', 'message', 'admin_id'];

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
