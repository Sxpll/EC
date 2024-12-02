<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ProductImage;


class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'availability' => 'available',
            'isActive' => true,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            ProductImage::factory()->count(1)->create([
                'product_id' => $product->id,
            ]);
        });
    }
}
