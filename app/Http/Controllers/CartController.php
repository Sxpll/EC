<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('cart.index', compact('cart'));
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

        $cart = session()->get('cart', []);

        if (!isset($cart[$id])) {
            $cart[$id] = [
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'image' => $product->images->first() ? 'data:' . $product->images->first()->mime_type . ';base64,' . $product->images->first()->file_data : 'https://via.placeholder.com/150',
            ];
        } else {
            $cart[$id]['quantity'] = $cart[$id]['quantity'] === 1 ? 1 : ++$cart[$id]['quantity'];
        }



        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Produkt został dodany do koszyka.');
    }


    public function update(Request $request, $id)
    {
        $cart = session()->get('cart');
        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = $request->quantity;
            $cart[$id]['subtotal'] = $cart[$id]['price'] * $cart[$id]['quantity'];
            session()->put('cart', $cart);

            $total = array_sum(array_column($cart, 'subtotal'));
            return response()->json([
                'success' => true,
                'item' => $cart[$id],
                'total' => $total
            ]);
        }
        return response()->json(['success' => false]);
    }



    public function remove(Request $request, $id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
            return back()->with('success', 'Produkt został usunięty z koszyka!');
        }

        return back()->with('error', 'Produkt nie znajduje się w koszyku!');
    }

    public function clear()
    {
        session()->forget('cart');
        return back()->with('success', 'Koszyk został wyczyszczony!');
    }

    public function contents()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return response()->json([
            'cart' => $cart,
            'total' => $total,
        ]);
    }
}
