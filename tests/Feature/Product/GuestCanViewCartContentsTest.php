<?php

namespace Tests\Feature\Cart;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class GuestCanViewCartContentsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_can_view_cart_contents()
    {
        $response = $this->get('/cart/contents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'cart',
                'total',  
            ])
            ->assertJson([
                'cart' => [],
                'total' => 0,
            ]);
    }
}
