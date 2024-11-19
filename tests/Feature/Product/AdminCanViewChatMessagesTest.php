<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanViewChatMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_chat_messages()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $chat = \App\Models\Chat::factory()->create();

        $response = $this->actingAs($admin)->get("/chat/{$chat->id}/messages");

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'message', 'created_at']]]);
    }
}
