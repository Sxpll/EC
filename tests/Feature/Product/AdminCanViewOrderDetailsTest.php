<?php

namespace Tests\Feature\Order;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AdminCanViewOrderDetailsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_view_order_details()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $orderItem = OrderItem::factory()->create();

        $response = $this->actingAs($admin)->get("/admin/orders/{$orderItem->order_id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.show');
        $response->assertViewHas('order', function ($order) use ($orderItem) {
            return $order->id === $orderItem->order_id;
        });
    }
}
