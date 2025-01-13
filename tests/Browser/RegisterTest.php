<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Faker\Factory as Faker;

class RegisterTest extends DuskTestCase
{
    public function testUserFlow()
    {
        $this->browse(function (Browser $browser) {
            $faker = Faker::create();

            $name = $faker->firstName;
            $lastname = $faker->lastName;
            $email = $faker->unique()->safeEmail;
            $password = 'password123';

            $browser->visit('/register')
                ->assertSee('Create your account')
                ->type('name', $name)
                ->type('lastname', $lastname)
                ->pause(1000)
                ->type('email', $email)
                ->pause(1000)
                ->type('password', $password)
                ->pause(1000)
                ->type('password_confirmation', $password)
                ->pause(1000)
                ->press('Register')
                ->pause(1000)
                ->assertPathIs('/home')
                ->assertSee('Welcome');

            // Przejście do sekcji "My Account"
            $browser->click('.account-icon')
                ->assertPathIs('/account')
                ->assertSee('My Account')
                ->assertInputValue('name', $name)
                ->assertInputValue('lastname', $lastname)
                ->assertInputValue('email', $email)
                ->pause(1000);

            // Zmiana imienia
            $newName = $faker->firstName;
            $browser->pause(2000)
                ->clear('input[name="name"]')
                ->type('name', $newName)
                ->click('[data-testid="update-account-button"]')
                ->pause(2000)
                ->refresh()
                ->assertInputValue('name', $newName);
        });
    }
}
