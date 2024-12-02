<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class AdminAccessProductsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    #[Test]
    public function admin_can_access_admin_products_page()
    {
        $response = $this->actingAs($this->admin)->get('/admin/products');

        $response->assertStatus(200);
    }
}
