<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCanViewHomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_home_page()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
        $response->assertViewIs('home');
    }
}
