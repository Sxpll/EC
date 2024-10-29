<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CartService
{
    public function getCart()
    {
        return Session::get('cart', []);
    }

    public function addProductToCart($product)
    {
        $cart = $this->getCart();
        $productId = $product->id;

        if (!isset($cart[$productId])) {
            $cart[$productId] = [
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'image' => $product->images->first() ? 'data:' . $product->images->first()->mime_type . ';base64,' . $product->images->first()->file_data : 'https://via.placeholder.com/150',
            ];
        }

        Session::put('cart', $cart);
    }

    public function updateProductQuantity($productId, $quantity)
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $quantity;
            $cart[$productId]['subtotal'] = $cart[$productId]['price'] * $quantity;
            Session::put('cart', $cart);

            return $cart[$productId];
        }

        return null;
    }

    public function removeProductFromCart($productId)
    {
        $cart = $this->getCart();
        unset($cart[$productId]);
        Session::put('cart', $cart);
    }

    public function clearCart()
    {
        Session::forget('cart');
        Session::forget('discount_code');
        Session::forget('discount_amount');
        Session::forget('discount_code_id');
    }

    public function calculateTotal($discountAmount = 0)
    {
        $cart = $this->getCart();
        $total = array_reduce($cart, function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);

        return max($total - $discountAmount, 0);
    }
}
