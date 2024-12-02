<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use PHPUnit\Framework\Attributes\Test;
class PublicViewProductsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function public_can_view_products_list()
    {
        $product = Product::factory()->create();

        $response = $this->get('/products2');

        $response->assertStatus(200);
        $response->assertSee($product->name);
    }
}
