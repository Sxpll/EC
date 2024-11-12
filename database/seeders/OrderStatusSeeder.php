<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderStatus;

class OrderStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            ['name' => 'W realizacji', 'code' => 'in_progress'],
            ['name' => 'W drodze', 'code' => 'on_the_way'],
            ['name' => 'Dostarczono', 'code' => 'delivered'],
        ];

        foreach ($statuses as $status) {
            $orderStatus = OrderStatus::firstOrNew(['code' => $status['code']]);
            $orderStatus->name = $status['name'];
            $orderStatus->save();
        }
    }
}
