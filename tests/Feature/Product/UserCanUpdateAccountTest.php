<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCanUpdateAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_account()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->put('/account', [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        $response->assertStatus(302); // Redirect after update
        $response->assertSessionHas('success', 'Account updated successfully.');
    }
}
