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

    // Dodawanie produktu do koszyka
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

        // Dodaj lub zaktualizuj produkt w koszyku
        if (!isset($cart[$id])) {
            $cart[$id] = [
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'image' => $product->images->first() ? 'data:' . $product->images->first()->mime_type . ';base64,' . $product->images->first()->file_data : 'https://via.placeholder.com/150',
            ];
        } else {
            $cart[$id]['quantity'] += 1;
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
            $total = $this->calculateTotal($cart);

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

    // Zawartość koszyka (dla AJAX)
    public function contents()
    {
        $cart = session()->get('cart', []);
        $total = $this->calculateTotal($cart);
        return response()->json([
            'cart' => $cart,
            'total' => $total,
        ]);
    }

    // Zastosowanie kodu rabatowego
    public function applyDiscount(Request $request)
    {
        $request->validate([
            'discount_code' => 'required|string',
        ]);

        $enteredCode = $request->input('discount_code');

        // Zastąp dotychczasowe pobieranie kodu tym fragmentem
        $discountCode = DiscountCode::where('is_active', true)->get()->first(function ($code) use ($enteredCode) {
            return Hash::check($enteredCode, $code->code_hash);
        });

        if (!$discountCode) {
            return redirect()->back()->with('error', 'Podany kod rabatowy jest nieprawidłowy lub nieaktywny.');
        }


        // Sprawdź daty ważności
        $now = Carbon::now();
        if ($discountCode->valid_from && $now->lt($discountCode->valid_from)) {
            return redirect()->back()->with('error', 'Kod rabatowy nie jest jeszcze aktywny.');
        }
        if ($discountCode->valid_until && $now->gt($discountCode->valid_until)) {
            return redirect()->back()->with('error', 'Kod rabatowy wygasł.');
        }

        // Sprawdź, czy kod jest przypisany do użytkownika (jeśli dotyczy)
        if ($discountCode->users()->count() > 0) {
            if (!Auth::check()) {
                return redirect()->back()->with('error', 'Musisz być zalogowany, aby użyć tego kodu rabatowego.');
            }
            if (!$discountCode->users->contains(Auth::id())) {
                return redirect()->back()->with('error', 'Ten kod rabatowy nie jest przypisany do Twojego konta.');
            }
        }

        // Oblicz wartość rabatu
        $cart = session()->get('cart', []);
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $discountAmount = 0;
        if ($discountCode->type == 'fixed') {
            $discountAmount = $discountCode->amount;
        } else { // 'percentage'
            $discountAmount = $total * ($discountCode->amount / 100);
        }

        // Upewnij się, że rabat nie przekracza łącznej kwoty
        $discountAmount = min($discountAmount, $total);

        // Zapisz kod i wartość rabatu w sesji
        session()->put('discount_code', $enteredCode);
        session()->put('discount_amount', $discountAmount);
        session()->put('discount_code_id', $discountCode->id);

        return redirect()->back()->with('success', 'Kod rabatowy został zastosowany.');
    }

    // Usunięcie kodu rabatowego
    public function removeDiscount()
    {
        session()->forget('discount_code');
        session()->forget('discount_amount');
        session()->forget('discount_code_id');

        return redirect()->back()->with('success', 'Kod rabatowy został usunięty.');
    }

    // Metoda pomocnicza do obliczania łącznej kwoty
    private function calculateTotal($cart)
    {
        $total = 0;
        foreach ($cart as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;
        }

        // Uwzględnij rabat
        $discountAmount = session('discount_amount', 0);
        $totalAfterDiscount = $total - $discountAmount;

        return max($totalAfterDiscount, 0); // Upewnij się, że total nie jest ujemny
    }
}
