<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanViewCategoryProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_category_products()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $category = \App\Models\Category::factory()->create();

        $response = $this->actingAs($admin)->get("/categories/{$category->id}/products");

        $response->assertStatus(200);
        $response->assertViewIs('categories.products');
    }
}
