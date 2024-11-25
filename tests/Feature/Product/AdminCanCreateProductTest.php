<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;


class AdminCanCreateProductTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    #[Test]
    public function admin_can_create_a_new_product()
    {
        $category = \App\Models\Category::factory()->create();

        $response = $this->actingAs($this->admin)->post('/admin/products', [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 10.00,
            'availability' => 'available',
            'categories' => [$category->id],
            'isActive' => 1,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }
}
