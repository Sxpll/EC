<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;
use App\Models\Product;



class CartService
{
    public function getCart(): array
    {
        if (Auth::check()) {
            return $this->getCartFromDatabase();
        } else {
            $cartItems = $this->getCartFromCookies();
            $productIds = array_keys($cartItems);
            $products = Product::whereIn('id', $productIds)->with('images')->get();

            $cart = [];
            foreach ($products as $product) {
                $quantity = $cartItems[$product->id];
                $cart[$product->id] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'image' => $product->images->first()
                        ? 'data:' . $product->images->first()->mime_type . ';base64,' . $product->images->first()->file_data
                        : 'https://via.placeholder.com/150',
                ];
            }
            return $cart;
        }
    }



    public function addProductToCart($product)
    {

        Auth::check() ? $this->addProductToCartInDatabase($product) : $this->addProductToCartInCookies($product);
    }

    public function updateProductQuantity($productId, $quantity)
    {
        return Auth::check() ? $this->updateProductQuantityInDatabase($productId, $quantity) : $this->updateProductQuantityInCookies($productId, $quantity);
    }

    public function removeProductFromCart($productId)
    {
        Auth::check() ? $this->removeProductFromCartInDatabase($productId) : $this->removeProductFromCartInCookies($productId);
    }

    public function clearCart()
    {
        Auth::check() ? $this->clearCartInDatabase() : $this->clearCartInCookies();
    }

    public function getCartFromDatabase()
    {
        $cartItems = CartItem::with('product')->where('user_id', Auth::id())->get();
        $cart = [];
        foreach ($cartItems as $item) {
            $product = $item->product;
            $cart[$item->product_id] = [
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => $item->quantity,
                'price' => $product->price,
                'image' => $product->images->first()
                    ? 'data:' . $product->images->first()->mime_type . ';base64,' . $product->images->first()->file_data
                    : 'https://via.placeholder.com/150',
            ];
        }
        return $cart;
    }

    public function addProductToCartInDatabase($product, $quantity = 1)
    {

        $cartItem = CartItem::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            CartItem::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }
    }

    public function useCookieCart()
    {
        $cookieCart = $this->getCartFromCookies();

        foreach ($cookieCart as $productId => $quantity) {
            $product = Product::find($productId);
            if ($product) {
                $this->addProductToCartInDatabase($product, $quantity);
            }
        }

        $this->clearCartInCookies();
    }




    public function updateProductQuantityInDatabase($productId, $quantity)
    {
        $cartItem = CartItem::where('user_id', Auth::id())->where('product_id', $productId)->first();

        if ($cartItem) {
            $cartItem->quantity = $quantity;
            $cartItem->save();

            return [
                'id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->product->price,
                'subtotal' => $cartItem->product->price * $cartItem->quantity,
            ];
        }
        return null;
    }

    public function removeProductFromCartInDatabase($productId)
    {
        CartItem::where('user_id', Auth::id())->where('product_id', $productId)->delete();
    }

    public function clearCartInDatabase()
    {
        CartItem::where('user_id', Auth::id())->delete();
    }

    public function getCartFromCookies()
    {
        $cart = Cookie::get('cart');
        if ($cart) {
            $cartJson = base64_decode($cart);
            $decodedCart = json_decode($cartJson, true);
        } else {
            $decodedCart = [];
        }
        return $decodedCart;
    }




    public function addProductToCartInCookies($product, $quantity = 1)
    {
        $cart = $this->getCartFromCookies();
        $productId = $product->id;

        if (isset($cart[$productId])) {
            $cart[$productId] += $quantity;
        } else {
            $cart[$productId] = $quantity;
        }

        $this->saveCartToCookies($cart);
    }




    public function updateProductQuantityInCookies($productId, $quantity)
    {
        $cart = $this->getCartFromCookies();

        if (isset($cart[$productId])) {
            $cart[$productId] = $quantity;
            $this->saveCartToCookies($cart);

            $product = Product::find($productId);
            if ($product) {
                return [
                    'id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'subtotal' => $product->price * $quantity,
                ];
            }
        }
        return ['error' => 'Produkt nie znaleziony w koszyku'];
    }






    public function removeProductFromCartInCookies($productId)
    {
        $cart = $this->getCartFromCookies();
        unset($cart[$productId]);
        $this->saveCartToCookies($cart);
    }

    public function clearCartInCookies()
    {
        Cookie::queue(Cookie::forget('cart'));
    }

    private function saveCartToCookies($cart)
    {
        $minutes = 60 * 24 * 7; // 7 dni
        $cartJson = json_encode($cart);
        $cartBase64 = base64_encode($cartJson);
        Cookie::queue(cookie('cart', $cartBase64, $minutes, '/', null, false, false));
    }



    public function mergeCarts($databaseCart, $cookieCart)
    {
        $mergedCart = [];

        // Dodaj produkty z koszyka w bazie danych
        foreach ($databaseCart as $productId => $item) {
            $mergedCart[$productId] = $item;
        }

        // Dodaj lub zaktualizuj produkty z koszyka z ciasteczek
        foreach ($cookieCart as $productId => $quantity) {
            if (isset($mergedCart[$productId])) {
                // Sumujemy ilości
                $mergedCart[$productId]['quantity'] += $quantity;
            } else {
                // Pobieramy szczegóły produktu
                $product = Product::find($productId);
                if ($product) {
                    $mergedCart[$productId] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'quantity' => $quantity,
                        'price' => $product->price,
                        'image' => $product->images->first()
                            ? 'data:' . $product->images->first()->mime_type . ';base64,' . $product->images->first()->file_data
                            : 'https://via.placeholder.com/150',
                    ];
                }
            }
        }

        return $mergedCart;
    }



    public function calculateTotal($cart = null, $discountAmount = 0)
    {
        if ($cart === null) {
            if (Auth::check()) {
                $cart = $this->getCartFromDatabase();
            } else {
                $cartItems = $this->getCartFromCookies();
                $productIds = array_keys($cartItems);
                $products = Product::whereIn('id', $productIds)->get();

                $cart = [];
                foreach ($products as $product) {
                    $quantity = $cartItems[$product->id];
                    $cart[$product->id] = [
                        'price' => $product->price,
                        'quantity' => $quantity,
                    ];
                }
            }
        }

        \Log::info('Koszyk przed obliczeniem total', ['cart' => $cart]);

        $total = array_reduce($cart, function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);

        \Log::info('Obliczony total', ['total' => $total, 'discountAmount' => $discountAmount]);

        return max($total - $discountAmount, 0);
    }
}
