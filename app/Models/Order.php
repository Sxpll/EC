<?php

// Order.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total',
        'status',
        'customer_name',
        'customer_email',
        'customer_address',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
