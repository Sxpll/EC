<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCanViewOwnOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_orders()
    {
        $user = \App\Models\User::factory()->create();
        \App\Models\Order::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/orders/my-orders');

        $response->assertStatus(200);
        $response->assertViewIs('orders.my-orders');
    }
}
