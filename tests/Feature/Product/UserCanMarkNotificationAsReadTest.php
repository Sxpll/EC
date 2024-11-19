<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCanMarkNotificationAsReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_notification_as_read()
    {
        $user = \App\Models\User::factory()->create();
        $notification = \App\Models\Notification::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/notifications/{$notification->id}/mark-as-read");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
