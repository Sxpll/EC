<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Faker\Factory as Faker;

class UserTest extends DuskTestCase
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
                ->type('email', $email)
                ->type('password', $password)
                ->type('password_confirmation', $password)
                ->press('Register')
                ->pause(1000)
                ->assertPathIs('/home')
                ->assertSee('Welcome');
        });
    }
}
