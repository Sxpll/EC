<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\CartService;
use App\Http\Controllers\DiscountCodeController;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart = $this->cartService->getCart();


        $total = count($cart) > 0
            ? $this->cartService->calculateTotal($cart, session('discount_amount', 0))
            : 0;

        return view('cart.index', compact('cart', 'total'));
    }




    public function add(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return redirect()->back()->with('error', 'Produkt nie został znaleziony.');
        }

        if ($product->availability === 'unavailable') {
            return redirect()->back()->with('error', 'Ten produkt jest niedostępny i nie może zostać dodany do koszyka.');
        }

        $this->cartService->addProductToCart($product);

        return redirect()->back()->with('success', 'Produkt został dodany do koszyka.');
    }

    public function update(Request $request, $id)
    {
        $quantity = $request->input('quantity', 1);

        $item = $this->cartService->updateProductQuantity($id, $quantity);

        if ($item && !isset($item['error'])) {
            $total = $this->cartService->calculateTotal();
            return response()->json([
                'success' => true,
                'item' => $item,
                'total_formatted' => number_format($total, 2),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $item['error'] ?? 'Nie udało się zaktualizować koszyka.'
        ]);
    }





    public function remove(Request $request, $id)
    {
        $this->cartService->removeProductFromCart($id);
        return back()->with('success', 'Produkt został usunięty z koszyka!');
    }

    public function clear()
    {
        $this->cartService->clearCart();
        return back()->with('success', 'Koszyk został wyczyszczony!');
    }

    public function contents()
    {
        $cart = $this->cartService->getCart();

        $total = $this->cartService->calculateTotal($cart, session('discount_amount', 0));

        return response()->json([
            'cart' => $cart,
            'total' => $total,
        ]);
    }

    public function mergeOptions()
    {
        $cartService = $this->cartService;

        // Pobierz koszyki z sesji
        $cookieCartItems = session('cookieCart', []);
        $databaseCartItems = session('databaseCart', []);

        // Pobierz szczegóły produktów dla koszyka z ciasteczek
        $cookieCart = [];
        if (!empty($cookieCartItems)) {
            $productIds = array_keys($cookieCartItems);
            $products = Product::whereIn('id', $productIds)->get();

            foreach ($products as $product) {
                $quantity = $cookieCartItems[$product->id];
                $cookieCart[$product->id] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ];
            }
        }

        // Koszyk z bazy danych już zawiera szczegóły produktów
        $databaseCart = $databaseCartItems;

        return view('cart.merge_options', compact('cookieCart', 'databaseCart'));
    }


    public function useCookieCart()
    {
        $cookieCart = $this->cartService->getCartFromCookies();
        $this->cartService->clearCartInDatabase();

        foreach ($cookieCart as $productId => $quantity) {
            $product = Product::find($productId);
            if ($product) {
                $this->cartService->addProductToCartInDatabase($product, $quantity);
            }
        }

        $this->cartService->clearCartInCookies();
        return redirect()->route('cart.index')->with('success', 'Twój koszyk został zaktualizowany.');
    }

    public function useDatabaseCart()
    {
        $this->cartService->clearCartInCookies();
        return redirect()->route('cart.index')->with('success', 'Używasz koszyka zapisanego w koncie.');
    }

    public function mergeCarts()
    {
        $cookieCart = $this->cartService->getCartFromCookies();
        $databaseCart = $this->cartService->getCartFromDatabase();

        $mergedCart = $this->cartService->mergeCarts($databaseCart, $cookieCart);

        $this->cartService->clearCartInDatabase();

        foreach ($mergedCart as $item) {
            $product = Product::find($item['id']);
            if ($product) {
                $this->cartService->addProductToCartInDatabase($product, $item['quantity']);
            }
        }

        $this->cartService->clearCartInCookies();
        return redirect()->route('cart.index')->with('success', 'Twoje koszyki zostały połączone.');
    }

    public function useSelectedCart(Request $request)
    {
        $option = $request->input('cart_option');
        $cartService = $this->cartService;

        switch ($option) {
            case 'database':
                // Użyj koszyka z bazy danych - czyścimy koszyk z ciasteczek
                $cartService->clearCartInCookies();
                $message = 'Kontynuujesz z koszykiem zapisanym na koncie.';
                break;

            case 'cookie':
                // Użyj koszyka z ciasteczek - przenosimy go do bazy danych
                $cartService->clearCartInDatabase();
                $cartService->useCookieCart();
                $message = 'Kontynuujesz z koszykiem sprzed zalogowania.';
                break;

            case 'merge':
                // Scal oba koszyki
                $cookieCart = session('cookieCart', []);
                $databaseCart = session('databaseCart', []);

                $mergedCart = $cartService->mergeCarts($databaseCart, $cookieCart);

                $cartService->clearCartInDatabase();

                foreach ($mergedCart as $item) {
                    $product = Product::find($item['id']);
                    if ($product) {
                        $cartService->addProductToCartInDatabase($product, $item['quantity']);
                    }
                }

                $cartService->clearCartInCookies();
                $message = 'Twoje koszyki zostały połączone.';
                break;

            default:
                // Nieznana opcja - przekieruj z powrotem z błędem
                return redirect()->back()->with('error', 'Nie wybrano prawidłowej opcji.');
        }

        // Czyść koszyki z sesji
        session()->forget('cookieCart');
        session()->forget('databaseCart');

        return redirect()->route('cart.index')->with('success', $message);
    }
}
