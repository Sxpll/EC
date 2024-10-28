<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\DiscountCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        // Dodaj produkt do koszyka tylko jeśli nie istnieje, ustawiając domyślną ilość na 1
        if (!isset($cart[$id])) {
            $cart[$id] = [
                'name' => $product->name,
                'quantity' => 1, // Ustawiamy ilość na 1 przy pierwszym dodaniu
                'price' => $product->price,
                'image' => $product->images->first() ? 'data:' . $product->images->first()->mime_type . ';base64,' . $product->images->first()->file_data : 'https://via.placeholder.com/150',
            ];
        }

        // Zapisz koszyk w sesji
        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Produkt został dodany do koszyka.');
    }

    // Aktualizacja ilości produktu w koszyku
    public function update(Request $request, $id)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = $request->quantity;
            $cart[$id]['subtotal'] = $cart[$id]['price'] * $cart[$id]['quantity'];
            session()->put('cart', $cart);

            // Oblicz łączną kwotę uwzględniając rabat
            $total = app(DiscountCodeController::class)->calculateTotal();

            return response()->json([
                'success' => true,
                'item' => $cart[$id],
                'total_formatted' => number_format($total, 2) . ' zł',
            ]);
        }
        return response()->json(['success' => false]);
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

    public function clear()
    {
        session()->forget('cart');
        session()->forget('discount_code');
        session()->forget('discount_amount');
        session()->forget('discount_code_id');
        return back()->with('success', 'Koszyk został wyczyszczony!');
    }

    public function contents()
    {
        $total = app(DiscountCodeController::class)->calculateTotal();
        $cart = session()->get('cart', []);

        return response()->json([
            'cart' => $cart,
            'total' => $total,
        ]);
    }
}
