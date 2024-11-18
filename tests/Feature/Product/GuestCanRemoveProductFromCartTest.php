<?php

namespace Tests\Feature\Cart;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use PHPUnit\Framework\Attributes\Test;

class GuestCanRemoveProductFromCartTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_can_remove_product_from_cart()
    {
        $product = Product::factory()->create();

       
        $this->post('/cart/add/' . $product->id);


        $response = $this->post('/cart/remove/' . $product->id);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
    }
}
