<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanUpdateCategoryHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_category_hierarchy()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $category = \App\Models\Category::factory()->create();

        $response = $this->actingAs($admin)->post('/categories/update-hierarchy', [
            'category_id' => $category->id,
            'new_parent_id' => null,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
