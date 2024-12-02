<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Tworzenie kilku kategorii
        Category::factory()->count(1)->create();
    }
}
