<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanManageChatMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_chat_messages()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $chat = \App\Models\Chat::factory()->create();

        $response = $this->actingAs($admin)->put("/chat/{$chat->id}/manage", [
            'status' => 'resolved',
            'is_taken' => true,
            'admin_id' => $admin->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_chats', [
            'id' => $chat->id,
            'status' => 'resolved',
            'is_taken' => true,
            'admin_id' => $admin->id,
        ]);
    }
}
