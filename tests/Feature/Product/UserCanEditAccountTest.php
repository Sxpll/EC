<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCanEditAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_edit_account()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/account');

        $response->assertStatus(200);
        $response->assertViewIs('account.edit');
    }
}
