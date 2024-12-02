<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->firstName,
            'lastname' => $this->faker->lastName, 
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'user',
            'isActive' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin()
    {
        return $this->state(fn() => [
            'role' => 'admin',
        ]);
    }
}
