<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCanViewChatMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_chat_messages()
    {
        $user = \App\Models\User::factory()->create();
        $chat = \App\Models\Chat::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/chat/{$chat->id}/messages");

        $response->assertStatus(200);
        $response->assertJsonStructure(['messages']);
    }
}
