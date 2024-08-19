<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttachment extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'file_data', 'mime_type', 'file_name'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
