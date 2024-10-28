<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCodeUsage extends Model
{
    protected $fillable = [
        'discount_code_id',
        'user_id',
        'order_id',
        'discount_amount',
    ];

    /**
     * Relacja do kodu rabatowego.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function discountCode()
    {
        return $this->belongsTo(DiscountCode::class);
    }

    /**
     * Relacja do użytkownika.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacja do zamówienia.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
