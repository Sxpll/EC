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
                ->pause(3000)
                ->type('email', $email)
                ->pause(3000)
                ->type('password', $password)
                ->pause(3000)
                ->type('password_confirmation', $password)
                ->pause(3000)
                ->press('Register')
                ->pause(3000)
                ->assertPathIs('/home')
                ->assertSee('Welcome');

            // Przejście do sekcji "My Account"
            $browser->click('.account-icon')
                ->assertPathIs('/account')
                ->assertSee('My Account')
                ->assertInputValue('name', $name)
                ->assertInputValue('lastname', $lastname)
                ->assertInputValue('email', $email)
                ->pause(3000);

            // Zmiana imienia
            $newName = $faker->firstName;
            $browser->pause(3000)
                ->clear('input[name="name"]')
                ->pause(3000)
                ->type('name', $newName)
                ->click('[data-testid="update-account-button"]')
                ->pause(3000)
                ->refresh()
                ->assertInputValue('name', $newName);
        });
    }
}
