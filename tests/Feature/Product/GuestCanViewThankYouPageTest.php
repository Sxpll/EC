<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GuestCanViewThankYouPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_thank_you_page()
    {
        $response = $this->get('/order/thankyou');

        $response->assertStatus(200);
        $response->assertViewIs('orders.thankyou');
    }
}
