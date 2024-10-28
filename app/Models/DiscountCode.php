<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;

class DiscountCode extends Model
{
    protected $fillable = [
        'code',
        'code_hash',
        'description',
        'amount',
        'type',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    // Rzutowanie pól na typ daty
    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    // Ukrycie pola 'code_hash' przy serializacji
    protected $hidden = ['code_hash'];

    /**
     * Relacja wiele-do-wielu z użytkownikami.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'discount_code_user');
    }

    /**
     * Relacja do użyć kodu rabatowego.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usages()
    {
        return $this->hasMany(DiscountCodeUsage::class);
    }

    /**
     * Ustawienie kodu i jego hasha.
     *
     * @param string $value
     * @return void
     */
    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = $value;
        $this->attributes['code_hash'] = Hash::make($value);
    }
}
