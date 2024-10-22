<?php

// Order.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class Order extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_address',
        'total',
        'status',
        'user_id',
        'pickup_code',
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
