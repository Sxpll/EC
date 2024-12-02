<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class GuestCanViewWelcomePageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_can_view_welcome_page()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Welcome'); // lub zmień na coś specyficznego dla widoku
    }
}
