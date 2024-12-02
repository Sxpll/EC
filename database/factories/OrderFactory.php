<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
            'customer_address' => $this->faker->address(),
            'total' => $this->faker->randomFloat(2, 50, 500),
            'status_id' => 1,
            'user_id' => User::factory(),
            'pickup_code' => Str::random(6),
            'discount_code_id' => null,
            'discount_amount' => 0,
        ];
    }
}
