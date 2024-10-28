<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class DiscountCode extends Model
{
    protected $fillable = [
        'code',
        'code_hash',
        'description',
        'amount',
        'type',
        'valid_from',
        'valid_until',
        'is_active',
        'is_single_use',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    protected $hidden = ['code_hash'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'discount_code_user');
    }

    public function usages()
    {
        return $this->hasMany(DiscountCodeUsage::class);
    }

    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = $value;
        $this->attributes['code_hash'] = Hash::make($value);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'discount_code_category', 'discount_code_id', 'category_id');
    }





    public function isApplicableToProduct(Product $product)
    {
        $applicableCategoryIds = $this->getApplicableCategoryIds();

        foreach ($product->categories as $productCategory) {
            if (in_array($productCategory->id, $applicableCategoryIds)) {
                return true;
            }
        }

        return false;
    }

    private function getApplicableCategoryIds()
    {
        $applicableCategories = collect();

        foreach ($this->categories as $category) {
            $applicableCategories->push($category->id);
            $applicableCategories = $applicableCategories->merge($category->descendants->pluck('id'));
        }

        return $applicableCategories->unique()->toArray();
    }

    public function calculateDiscountAmount($total)
    {
        return $this->type === 'fixed'
            ? $this->amount
            : $total * ($this->amount / 100);
    }
}
