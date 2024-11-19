<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanUpdateCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_category()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $category = \App\Models\Category::factory()->create();

        $response = $this->actingAs($admin)->put("/categories/{$category->id}", [
            'name' => 'Updated Category Name',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
