<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;

class AdminCanAccessDashboardTest extends TestCase
{
    public function test_admin_can_access_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }
}
