<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategoryHistory extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'category_id', 'status', 'assigned_at', 'removed_at'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
