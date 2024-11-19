<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicCanAccessHomePageTest extends TestCase
{
    public function test_can_access_home_page()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('welcome');
    }
}
