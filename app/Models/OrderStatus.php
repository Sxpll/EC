<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $fillable = ['name', 'code'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'status_id');
    }
}
