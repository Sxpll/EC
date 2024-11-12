<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;



class Order extends Model
{

    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_address',
        'total',
        'status_id',
        'user_id',
        'pickup_code',
        'discount_code_id',
        'discount_amount',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function discountCode()
    {
        return $this->belongsTo(DiscountCode::class);
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }
}
