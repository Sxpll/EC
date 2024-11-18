<?php

namespace Tests\Feature\Cart;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use PHPUnit\Framework\Attributes\Test;

class GuestCanUpdateProductQuantityInCartTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_can_update_product_quantity_in_cart()
    {
        $product = Product::factory()->create();

        $this->post('/cart/add/' . $product->id)
            ->assertStatus(302); // Zakładam, że przekierowuje po dodaniu

        $response = $this->post('/cart/update/' . $product->id, [
            'quantity' => 3,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
            'item' => [
                'id' => $product->id,
                'quantity' => 3,
            ],
        ]);
    }
}
