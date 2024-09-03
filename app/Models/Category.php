<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'parent_id', 'isActive', 'order'];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        // Filtruj dzieci na podstawie isActive
        return $this->hasMany(Category::class, 'parent_id')->where('isActive', 1)->with('childrenRecursive');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product', 'category_id', 'product_id');
    }

    // Nowa metoda do pobierania tylko aktywnych kategorii
    public function scopeActive($query)
    {
        return $query->where('isActive', 1);
    }

    // Sprawdzenie, czy kategoria ma przypisane produkty
    public function hasProducts()
    {
        return $this->products()->exists();
    }

    // Sprawdzenie, czy kategoria jest root
    public function isRoot()
    {
        return is_null($this->parent_id);
    }
}
