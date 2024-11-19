<?php

namespace Tests\Feature\Cart;

use Tests\TestCase;

class GuestCanApplyDiscountCodeTest extends TestCase
{
    public function test_guest_can_apply_discount_code()
    {
        $response = $this->post('/cart/apply-discount', [
            'code' => 'DISCOUNT123',
        ]);

        $response->assertStatus(200); 
    }
}
