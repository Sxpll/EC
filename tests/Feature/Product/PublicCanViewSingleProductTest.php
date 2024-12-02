<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use PHPUnit\Framework\Attributes\Test;

class PublicCanViewSingleProductTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function public_can_view_single_product()
    {
        $product = Product::factory()->create(['name' => 'Test Single Product']);

        $response = $this->get('/public/products/' . $product->id);

        $response->assertStatus(200);
        $response->assertSee('Test Single Product'); 
    }
}
