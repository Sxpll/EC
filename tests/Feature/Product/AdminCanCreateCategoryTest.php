<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanCreateCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_category()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/categories', [
            'name' => 'New Category',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }
}
