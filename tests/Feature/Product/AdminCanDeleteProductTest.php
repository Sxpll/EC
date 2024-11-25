<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use PHPUnit\Framework\Attributes\Test;

class AdminCanDeleteProductTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->product = Product::factory()->create();
    }

    #[Test]
    public function admin_can_delete_product()
    {
        $response = $this->actingAs($this->admin)->delete('/admin/products/' . $this->product->id);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('products', ['id' => $this->product->id, 'isActive' => true]);
    }
}
