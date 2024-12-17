<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Pobieranie wszystkich kategorii
        $categories = Category::all();

        // Tworzenie produktów i przypisywanie do kategorii
        Product::factory()->count(10)->create()->each(function ($product) use ($categories) {
            // Przypisz produkt do losowej kategorii
            $product->categories()->attach($categories->random()->id);
        });
    }
}
