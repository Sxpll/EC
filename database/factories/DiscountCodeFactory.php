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
            'code_hash' => bcrypt($this->faker->unique()->bothify('DISCOUNT-####')),
            'description' => $this->faker->sentence(),
            'amount' => $this->faker->randomFloat(2, 10, 100), // Kwota zniżki
            'type' => $this->faker->randomElement(['fixed', 'percentage']), // Typ zniżki
            'valid_from' => now(),
            'valid_until' => now()->addDays($this->faker->numberBetween(7, 30)), // Ważność
            'is_active' => $this->faker->boolean(80), // Aktywny w 80% przypadków
            'is_single_use' => $this->faker->boolean(50), // Jednorazowy
        ];
    }
}
