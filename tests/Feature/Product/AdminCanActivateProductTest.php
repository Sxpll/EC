<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use PHPUnit\Framework\Attributes\Test;

class AdminCanActivateProductTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->product = Product::factory()->create(['isActive' => 0]);
    }

    #[Test]
    public function admin_can_activate_product()
    {
        $response = $this->actingAs($this->admin)->post('/products/' . $this->product->id . '/activate');

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['id' => $this->product->id, 'isActive' => 1]);
    }
}
