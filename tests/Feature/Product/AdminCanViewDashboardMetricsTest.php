<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanViewDashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_metrics()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(5)->create();
        Product::factory()->count(10)->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHasAll([
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'deletedUsers',
            'totalProducts'
        ]);
    }
}
