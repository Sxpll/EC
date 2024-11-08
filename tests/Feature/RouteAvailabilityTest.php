<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;



class RouteAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testuje dostępność wszystkich publicznych tras.
     *
     * @return void
     */
    public function test_public_routes_are_accessible()
    {
        // Pobierz wszystkie zarejestrowane trasy


        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            // Sprawdź, czy trasa jest dostępna dla metod GET lub POST
            $methods = $route->methods();
            $uri = $route->uri();

            // Ignoruj trasy do API lub trasy zarejestrowane w innej domenie
            if (strpos($uri, 'api') === 0 || $route->getDomain() !== null) {
                continue;
            }

            // Ignoruj trasy wymagające uwierzytelnienia (middleware 'auth')
            $middlewares = $route->gatherMiddleware();
            if (in_array('auth', $middlewares)) {
                continue;
            }

            // Ignoruj trasy z parametrami, aby uniknąć problemów z brakującymi danymi
            if (strpos($uri, '{') !== false) {
                continue;
            }

            foreach ($methods as $method) {
                // Sprawdzamy tylko metody GET i POST
                if (!in_array($method, ['GET', 'POST'])) {
                    continue;
                }

                $response = $this->call($method, '/' . $uri);

                // Oczekujemy kodu statusu 200 OK lub 302 Found (przekierowanie)
                $this->assertTrue(
                    in_array($response->status(), [200, 302]),
                    "$method $uri zwróciło nieoczekiwany status {$response->status()}"
                );
            }
        }
    }
}
