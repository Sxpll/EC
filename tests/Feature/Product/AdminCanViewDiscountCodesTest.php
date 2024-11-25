<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCanViewDiscountCodesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_discount_codes()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        \App\Models\DiscountCode::factory()->count(5)->create();


        $response = $this->actingAs($admin)->get('/admin/discount_codes');

        $response->assertStatus(200);
        $response->assertViewIs('admin.discount_codes.index');
    }
}
