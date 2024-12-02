<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
class GuestCannotAccessAdminProductsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_cannot_access_admin_products_page()
    {
        $response = $this->get('/admin/products');

        $response->assertRedirect('/login'); 
    }
}
