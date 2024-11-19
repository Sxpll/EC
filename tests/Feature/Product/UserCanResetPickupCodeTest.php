<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCanResetPickupCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_reset_pickup_code()
    {
        $user = \App\Models\User::factory()->create();
        $order = \App\Models\Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/orders/{$order->id}/reset-pickup-code");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
