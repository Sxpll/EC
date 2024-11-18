<?php

namespace Tests\Feature\Cart;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use PHPUnit\Framework\Attributes\Test;

class GuestCanClearCartTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_can_clear_cart()
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // Dodaj produkty do koszyka
        $this->post('/cart/add/' . $product1->id);
        $this->post('/cart/add/' . $product2->id);

        // Wyczyść koszyk
        $response = $this->post('/cart/clear');

        $response->assertStatus(302); // Zakładam, że przekierowuje po wyczyszczeniu
        $this->assertEmpty(session('cart', [])); // Sprawdzenie, czy koszyk jest pusty
    }
}
