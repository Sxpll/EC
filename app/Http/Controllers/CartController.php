<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CartController extends Controller
{
    // Wyświetlanie koszyka
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('cart.index', compact('cart'));
    }

    public function add(Request $request, $id)
    {
        // Znajdź produkt
        $product = Product::find($id);
        if (!$product) {
            return redirect()->back()->with('error', 'Produkt nie został znaleziony.');
        }

        // Pobierz koszyk z sesji
        $cart = session()->get('cart', []);

        // Dodaj lub zaktualizuj produkt w koszyku
        if (isset($cart[$id])) {
            $cart[$id]['quantity']++;
        } else {
            $cart[$id] = [
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
            ];
        }

        // Zapisz koszyk w sesji
        session()->put('cart', $cart);

        // Przekieruj z komunikatem sukcesu
        return redirect()->back()->with('success', 'Produkt został dodany do koszyka.');
    }


    // Aktualizacja ilości produktu w koszyku
    public function update(Request $request, $id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
            return back()->with('success', 'Koszyk został zaktualizowany!');
        }

        return back()->with('error', 'Produkt nie znajduje się w koszyku!');
    }

    // Usuwanie produktu z koszyka
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

    // Czyszczenie koszyka
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
