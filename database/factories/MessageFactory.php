<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Message;
use App\Models\Chat;
use App\Models\User;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition()
    {
        return [
            'chat_id' => Chat::factory(),
            'user_id' => User::factory(),
            'message' => $this->faker->paragraph(),
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
