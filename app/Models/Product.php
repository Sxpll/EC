<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'category_id', 'isActive'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id');
    }


    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function attachments()
    {
        return $this->hasMany(ProductAttachment::class);
    }
}
