<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'admin_name',
        'admin_lastname',
        'action',
        'user_id',
        'user_name',
        'user_lastname',
        'old_value',
        'new_value',
    ];
}
