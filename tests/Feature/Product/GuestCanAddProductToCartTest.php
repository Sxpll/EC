<?php

namespace Tests\Feature\Cart;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use PHPUnit\Framework\Attributes\Test;

class GuestCanAddProductToCartTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_can_add_product_to_cart()
    {
        $product = Product::factory()->create();

        $response = $this->from('/')->post('/cart/add/' . $product->id);  

        $response->assertRedirect('/');
        $response->assertSessionHas('success', 'Produkt został dodany do koszyka.');
    }
}
