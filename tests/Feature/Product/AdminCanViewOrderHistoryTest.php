<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanViewOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_order_history()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        \App\Models\Order::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.index');
    }
}
