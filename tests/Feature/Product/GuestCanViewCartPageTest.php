<?php

namespace Tests\Feature\Cart;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class GuestCanViewCartPageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_can_view_cart_page()
    {
        $response = $this->get('/cart');

        $response->assertStatus(200);
        $response->assertSee('Twój koszyk'); // Zmień na odpowiedni tekst z widoku koszyka
    }
}
