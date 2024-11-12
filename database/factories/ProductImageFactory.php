<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductImage;
use Illuminate\Support\Str;

class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    public function definition()
    {
        return [
            'product_id' => null, 
            'file_data' => $this->faker->image(),
            'mime_type' => 'image/jpeg',
        ];
    }
}
