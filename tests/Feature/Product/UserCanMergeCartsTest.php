<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCanMergeCartsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_merge_carts()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->post('/cart/merge-carts');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
