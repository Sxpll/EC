<?php

namespace Tests\Feature\Cart;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class GuestCannotMergeCartsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_cannot_merge_carts()
    {
        // Wywołanie POST do /cart/merge-carts jako gość
        $response = $this->post('/cart/merge-carts');

        $response->assertRedirect('/login'); // Gość powinien być przekierowany na login
    }
}
