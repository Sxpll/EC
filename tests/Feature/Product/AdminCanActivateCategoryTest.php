<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanActivateCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_activate_category()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $category = \App\Models\Category::factory()->create(['is_active' => false]);

        $response = $this->actingAs($admin)->patch("/categories/{$category->id}/activate");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
