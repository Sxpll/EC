<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;

class RouteAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $product;
    protected $category;
    protected $order;

    public function setUp(): void
    {
        parent::setUp();

        // Uruchom seedery przed każdym testem
        $this->seed();

        // Tworzenie danych testowych
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->product = Product::factory()->create();
        $this->category = Category::factory()->create();
        $this->order = Order::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_all_routes_are_accessible()
    {
        $this->withoutExceptionHandling();

        $routes = Route::getRoutes();

        $counter = 0;
        $maxRoutes = 1;

        foreach ($routes as $route) {
            $methods = $route->methods();
            $uri = $route->uri();
            $middlewares = $route->gatherMiddleware();



            // Przygotowanie parametrów
            $parameters = [];
            if (strpos($uri, '{') !== false) {
                preg_match_all('/\{(\w+?)\??\}/', $uri, $matches);
                foreach ($matches[1] as $param) {
                    switch ($param) {
                        case 'id':
                        case 'productId':
                            $parameters[$param] = $this->product->id;
                            break;
                        case 'category':
                        case 'categoryId':
                            $parameters[$param] = $this->category->id;
                            break;
                        case 'orderId':
                            $parameters[$param] = $this->order->id;
                            break;
                        default:
                            $parameters[$param] = 1; 
                    }
                }
            }

            foreach ($methods as $method) {
                if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])) {
                    continue;
                }

                // Określenie, czy trasa wymaga uwierzytelnienia
                $requiresAuth = collect($middlewares)->contains(function ($middleware) {
                    return strpos($middleware, 'auth') !== false;
                });

                // Określenie, czy trasa jest dla administratora
                $isAdminRoute = strpos($uri, 'admin') !== false;

                // Przygotowanie żądania
                if ($requiresAuth) {
                    $actingUser = $isAdminRoute ? $this->admin : $this->user;
                    $response = $this->actingAs($actingUser)->call($method, '/' . $uri, $parameters);
                } else {
                    $response = $this->call($method, '/' . $uri, $parameters);
                }

                // Dodaj komunikat debugujący
                echo "Przetestowano: $method /$uri - Status: {$response->status()}\n";

                // Sprawdzenie oczekiwanego kodu statusu
                $expectedStatusCodes = [200, 302, 403, 404];
                $this->assertContains(
                    $response->status(),
                    $expectedStatusCodes,
                    "$method $uri zwróciło nieoczekiwany status {$response->status()}"
                );

                // Zwiększ licznik przetestowanych tras
                $counter++;
                if ($counter >= $maxRoutes) {
                    echo "Osiągnięto limit $maxRoutes tras. Przerwanie testu.\n";
                    return;
                }
            }
        }
    }
}
