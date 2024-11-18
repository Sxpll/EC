<?php

namespace Tests\Feature\Cart;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class GuestCannotAccessCartMergeOptionsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_cannot_access_cart_merge_options()
    {
        // Wywołanie widoku opcji łączenia koszyków jako gość
        $response = $this->get('/cart/merge-options');

        $response->assertRedirect('/login'); // Założenie, że goście są przekierowywani na login
    }
}
