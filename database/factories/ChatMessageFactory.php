<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ChatMessage;
use App\Models\Chat;
use App\Models\User;

class ChatMessageFactory extends Factory
{
    protected $model = ChatMessage::class;

    public function definition()
    {
        return [
            'chat_id' => Chat::factory(),
            'user_id' => User::factory(),
            'message' => $this->faker->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
