<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCanAccessDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_dashboard()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/user/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('user.dashboard');
    }
}
