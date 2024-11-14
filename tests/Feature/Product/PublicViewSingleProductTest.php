<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use PHPUnit\Framework\Attributes\Test;
class PublicViewSingleProductTest extends TestCase
{
    use RefreshDatabase;

    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        
        $this->product = Product::factory()->create();
    }

    #[Test]
    public function public_can_view_single_product()
    {
        $response = $this->get('/public/products/' . $this->product->id);

        $response->assertStatus(200);
        $response->assertSee($this->product->name);
    }
}
