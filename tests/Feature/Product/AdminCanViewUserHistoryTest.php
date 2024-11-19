<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanViewUserHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_history()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($admin)->get("/admin/user/{$user->id}/history");

        $response->assertStatus(200);
        $response->assertViewIs('admin.user.history');
    }
}
