<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Tworzenie 5 zamówień
        Order::factory()->count(1)->create();
    }
}
