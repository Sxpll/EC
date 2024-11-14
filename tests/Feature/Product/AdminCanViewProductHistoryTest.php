<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use PHPUnit\Framework\Attributes\Test;
class AdminCanViewProductHistoryTest extends TestCase
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
    public function admin_can_view_product_history()
    {
        $response = $this->actingAs($this->admin)->get('/products/' . $this->product->id . '/history');

        $response->assertStatus(200);

    }
}
