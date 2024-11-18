<?php

namespace Tests\Feature\Order;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanUpdateOrderStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_order_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create(['status_id' => 1]); // Initial status

        $response = $this->actingAs($admin)->post("/admin/orders/{$order->id}/update", [
            'status_id' => 2, // New status
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/orders');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status_id' => 2, // Updated status
        ]);
    }
}
