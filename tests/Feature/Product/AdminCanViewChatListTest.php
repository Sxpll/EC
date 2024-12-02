<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanViewChatListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_chat_list()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/chat');

        $response->assertStatus(200);
        $response->assertViewIs('chat.index');
    }
}
