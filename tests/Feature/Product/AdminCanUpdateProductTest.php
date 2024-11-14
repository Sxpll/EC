<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use PHPUnit\Framework\Attributes\Test;
class AdminCanUpdateProductTest extends TestCase
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
    public function admin_can_update_product()
    {
        $response = $this->actingAs($this->admin)->put('/products/' . $this->product->id, [
            'name' => 'Updated Product',

        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['id' => $this->product->id, 'name' => 'Updated Product']);
    }
}
