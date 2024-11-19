<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCanViewMyOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_my_orders()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/orders/my-orders');

        $response->assertStatus(200);
        $response->assertViewIs('orders.my-orders');
    }
}
