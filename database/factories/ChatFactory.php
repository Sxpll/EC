<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chat;
use App\Models\User;

class ChatFactory extends Factory
{
    protected $model = Chat::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'subject' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['open', 'resolved', 'pending']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
