<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanDeleteCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_category()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $category = \App\Models\Category::factory()->create();

        $response = $this->actingAs($admin)->delete("/categories/{$category->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
