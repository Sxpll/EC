<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductImage;
use App\Models\Product;

class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'file_data' => $this->faker->image(storage_path('app/public'), 640, 480, null, false),
            'mime_type' => 'image/jpeg', 
        ];
    }
}
