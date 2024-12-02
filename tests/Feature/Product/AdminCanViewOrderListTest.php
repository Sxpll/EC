<?php

namespace Tests\Feature\Order;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanViewOrderListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_order_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Order::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.index');
        $response->assertViewHas('orders', function ($orders) {
            return $orders->count() === 5;
        });
    }
}
