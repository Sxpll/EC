<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCanViewNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_notifications()
    {
        $user = \App\Models\User::factory()->create();
        \App\Models\Notification::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'message', 'created_at']]]);
    }
}
