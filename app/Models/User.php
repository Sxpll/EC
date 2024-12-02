<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\DiscountCode;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'role',
        'is_hr',
        'isActive',
        'is_deleted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relacja do czatów utworzonych przez użytkownika
    public function chats()
    {
        return $this->hasMany(Chat::class, 'user_id');
    }

    // Relacja do wiadomości wysłanych przez użytkownika
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Relacja do czatów, w których użytkownik jest administratorem
    public function managedChats()
    {
        return $this->hasMany(Chat::class, 'admin_id');
    }

    // Relacja wiele-do-wielu z kodami rabatowymi
    public function discountCodes(): BelongsToMany
    {
        return $this->belongsToMany(DiscountCode::class, 'discount_code_user');
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
