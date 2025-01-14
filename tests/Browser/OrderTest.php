<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Faker\Factory as Faker;

class OrderTest extends DuskTestCase
{
    public function testOrderFlow()
    {
        $this->browse(function (Browser $browser) {
            $faker = Faker::create();

            $browser->visit('/')
                ->assertSee('Products')
                ->click('[data-testid="products-link"]')
                ->assertPathIs('/products2')
                ->assertSee('Products');

            $browser->click('[data-testid="product-image"]')
                ->assertPathBeginsWith('/public/products/')
                ->assertSee('Dodaj do koszyka');

            $browser->click('[data-testid="add-to-cart-button"]')
                ->pause(3000)
                ->click('[data-testid="cart-icon"]')
                ->pause(3000)
                ->assertVisible('[data-testid="proceed-to-checkout-button"]')
                ->click('[data-testid="proceed-to-checkout-button"]');

            $browser->assertPathIs('/cart')
                ->pause(3000)
                ->type('discount_code', 'test')
                ->pause(3000)
                ->click('[data-testid="apply-discount-button"]')
                ->pause(3000);

            $browser->click('[data-testid="place-order-button"]')
                ->assertPathIs('/order/create');

            $email = $faker->userName . '@exmaple.com';

            $browser->type('customer_name', $faker->firstName . ' ' . $faker->lastName)
                ->pause(3000)
                ->type('customer_email', $email)
                ->pause(3000)
                ->type('customer_address', $faker->address)
                ->pause(3000)
                ->click('[data-testid="place-order-button2"]')
                ->pause(3000);
        });
    }
}
