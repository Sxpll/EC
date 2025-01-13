<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Faker\Factory as Faker;

class AdminUserTest extends DuskTestCase
{
    public function testAdminCreatesAndLogsInAsNewUser()
    {
        $this->browse(function (Browser $browser) {
            $faker = Faker::create();

            $name = $faker->firstName;
            $lastname = $faker->lastName;
            $email = $faker->unique()->safeEmail;
            $password = 'password123';

            $browser->visit('/login')
                ->type('email', 'admin@example.com')
                ->type('password', 'password123')
                ->press('[data-testid="login-button"]')
                ->assertPathIs('/home')
                ->click('[data-testid="admin-panel-link"]')
                ->assertPathIs('/admin/dashboard')
                ->click('[data-testid="manage-users-link"]')
                ->waitFor('#openModalBtn')
                ->click('#openModalBtn')
                ->type('name', $name)
                ->pause(500)
                ->type('lastname', $lastname)
                ->pause(500)
                ->type('email', $email)
                ->pause(500)
                ->type('password', $password)
                ->pause(500)
                ->select('role', 'user')
                ->check('isActive')
                ->press('[data-testid="add-user-button"]')
                ->pause(2000)
                ->click('[data-testid="logout-icon"]')
                ->assertPathIs('/')
                ->click('.account-icon')
                ->assertPathIs('/login')
                ->type('email', $email)
                ->type('password', $password)
                ->press('[data-testid="login-button"]')
                ->assertPathIs('/account')

                // Sprawdzenie danych użytkownika
                ->assertInputValue('name', $name) // Sprawdzenie imienia
                ->assertInputValue('lastname', $lastname) // Sprawdzenie nazwiska
                ->assertInputValue('email', $email) // Sprawdzenie emaila

                ->pause(4000);
        });
    }
}
