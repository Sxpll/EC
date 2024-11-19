<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GuestCanViewCategoriesTreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_categories_tree()
    {
        $response = $this->get('/categories/get-tree');

        $response->assertStatus(200);
        $response->assertJsonStructure(['categories']);
    }
}
