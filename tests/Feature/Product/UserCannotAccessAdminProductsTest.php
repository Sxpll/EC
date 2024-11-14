<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class UserCannotAccessAdminProductsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();


        $this->user = User::factory()->create(['role' => 'user']);
    }

    #[Test]
    public function user_cannot_access_admin_products_page()
    {
        $response = $this->actingAs($this->user)->get('/admin/products');

        $response->assertStatus(302); 
    }
}
