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

            // Przejdź na stronę główną i do produktów
            $browser->visit('/')
                ->assertSee('Products')
                ->click('[data-testid="products-link"]')
                ->assertPathIs('/products2')
                ->assertSee('Products');


            $browser->click('[data-testid="product-image"]')
                ->assertPathBeginsWith('/public/products/')
                ->assertSee('Dodaj do koszyka');


            $browser->click('[data-testid="add-to-cart-button"]')
                ->pause(1000)
                ->click('[data-testid="cart-icon"]')
                ->pause(1000)
                ->assertVisible('[data-testid="proceed-to-checkout-button"]')
                ->click('[data-testid="proceed-to-checkout-button"]');


            $browser->assertPathIs('/cart')
                ->type('discount_code', 'test')
                ->click('[data-testid="apply-discount-button"]')
                ->pause(1000);

            $browser->click('[data-testid="place-order-button"]')
                ->assertPathIs('/order/create');


            $email = $faker->userName . '@exmaple.com';

            $browser->type('customer_name', $faker->firstName . ' ' . $faker->lastName)
                ->type('customer_email', $email)
                ->type('customer_address', $faker->address)
                ->pause(1000)
                ->click('[data-testid="place-order-button2"]')
                ->pause(1000);
        });
    }
}
