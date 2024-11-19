<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DiscountCode;

class DiscountCodeFactory extends Factory
{
    protected $model = DiscountCode::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->bothify('DISCOUNT-####'),
            'description' => $this->faker->sentence(),
            'discount_percent' => $this->faker->numberBetween(5, 50),
            'valid_from' => now(),
            'valid_to' => now()->addDays($this->faker->numberBetween(7, 30)),
            'usage_limit' => $this->faker->randomElement([null, $this->faker->numberBetween(1, 10)]),
            'used_count' => 0,
        ];
    }
}
