<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DiscountCode;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\DiscountCodeMail;
use Illuminate\Support\Facades\Hash;
use App\Models\DiscountCodeUsage;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;



class DiscountCodeController extends Controller
{
    // Konstruktor kontrolera
    public function __construct()
    {
        // Zakładamy, że używasz middleware 'auth'
        $this->middleware('auth');
    }

    // Wyświetlanie listy kodów rabatowych (dla administratora)
    public function index()
    {
        // Sprawdzenie, czy użytkownik jest zalogowany i czy jest administratorem
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Nie masz dostępu do tej strony.');
        }

        $discountCodes = DiscountCode::all();
        return view('admin.discount_codes.index', compact('discountCodes'));
    }

    public function create()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Nie masz dostępu do tej strony.');
        }

        $users = User::all();
        $categories = $this->getJsTreeCategories();

        return view('admin.discount_codes.create', compact('users', 'categories'));
    }


    public function store(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Nie masz dostępu do tej strony.');
        }

        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'type' => 'required|in:fixed,percentage',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'required|boolean',
            'is_single_use' => 'required|boolean',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
            'categories' => 'nullable', // Usuwamy walidację 'array'
        ]);

        // Tworzenie kodów dla każdego użytkownika lub globalnie dla wszystkich
        $users = $request->has('users') ? User::whereIn('id', $request->input('users'))->get() : User::all();

        foreach ($users as $user) {
            do {
                $plainCode = Str::upper(Str::random(8));
                $codeHash = Hash::make($plainCode);
                $exists = DiscountCode::where('code_hash', $codeHash)->exists();
            } while ($exists);

            $discountCode = new DiscountCode([
                'description' => $request->input('description'),
                'amount' => $request->input('amount'),
                'type' => $request->input('type'),
                'valid_from' => $request->input('valid_from'),
                'valid_until' => $request->input('valid_until'),
                'is_active' => $request->boolean('is_active'),
                'is_single_use' => $request->input('is_single_use', 0),
                'code_hash' => $codeHash,
            ]);

            $discountCode->save();

            // Przetwarzanie kategorii, jeśli zostały wybrane
            if ($request->has('categories')) {
                $categories = json_decode($request->input('categories'), true);
                foreach ($categories as $categoryId) {
                    Log::info("Dodawanie kategorii {$categoryId} do kodu rabatowego {$discountCode->id}");
                    $discountCode->categories()->attach($categoryId);
                }
            }

            // Przypisanie kodu do użytkownika
            $discountCode->users()->attach($user->id);
            $user->has_new_discount = true;
            $user->save();

            // Wysyłanie e-maila z kodem do użytkownika
            Mail::to($user->email)->send(new DiscountCodeMail($plainCode, $discountCode));
        }

        return redirect()->route('discount_codes.index')->with('success', 'Kody rabatowe zostały wygenerowane i wysłane do użytkowników.');
    }





    public function edit($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Nie masz dostępu do tej strony.');
        }

        $discountCode = DiscountCode::findOrFail($id);
        $users = User::all();
        $categories = $this->getJsTreeCategories();
        return view('admin.discount_codes.edit', compact('discountCode', 'users', 'categories'));
    }

    private function getJsTreeCategories()
    {
        $categories = Category::whereNull('parent_id')->with('childrenRecursive')->get();

        $formatCategories = function ($categories) use (&$formatCategories) {
            return $categories->map(function ($category) use ($formatCategories) {
                return [
                    'id' => $category->id,
                    'text' => $category->name,
                    'children' => $category->childrenRecursive->isEmpty() ? [] : $formatCategories($category->childrenRecursive),
                ];
            });
        };

        return $formatCategories($categories)->toArray(); // Dodajemy `toArray()` dla poprawnego formatu JSON
    }



    // Aktualizacja kodu rabatowego (dla administratora)
    public function update(Request $request, $id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Nie masz dostępu do tej strony.');
        }

        $discountCode = DiscountCode::findOrFail($id);

        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'type' => 'required|in:fixed,percentage',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'required|boolean',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        $discountCode->update([
            'description' => $request->input('description'),
            'amount' => $request->input('amount'),
            'type' => $request->input('type'),
            'valid_from' => $request->input('valid_from'),
            'valid_until' => $request->input('valid_until'),
            'is_active' => $request->boolean('is_active'),
        ]);

        // Aktualizacja przypisanych użytkowników
        if ($request->has('users')) {
            $discountCode->users()->sync($request->input('users'));
        } else {
            $discountCode->users()->detach();
        }

        return redirect()->route('discount_codes.index')->with('success', 'Kod rabatowy został zaktualizowany.');
    }

    // Usuwanie kodu rabatowego (dla administratora)
    public function destroy($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Nie masz dostępu do tej strony.');
        }

        $discountCode = DiscountCode::findOrFail($id);
        $discountCode->delete();

        return redirect()->route('discount_codes.index')->with('success', 'Kod rabatowy został usunięty.');
    }

    public function myDiscountCodes()
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Musisz być zalogowany, aby zobaczyć swoje kody rabatowe.');
        }

        $user = Auth::user();
        $discountCodes = $user->discountCodes()->where('is_active', true)->get();

        // Pobranie historii użyć
        $usages = DiscountCodeUsage::where('user_id', $user->id)->with('discountCode', 'order')->get();

        // Resetowanie flagi nowego kodu po wyświetleniu strony
        if ($user->has_new_discount) {
            $user->has_new_discount = false;
            $user->save();
        }

        return view('discount_codes.my_codes', compact('discountCodes', 'usages'));
    }


    public function applyDiscountCode(Request $request)
    {
        $enteredCode = $request->input('discount_code');

        // Znalezienie kodu rabatowego na podstawie wpisanego kodu
        $discountCode = DiscountCode::where('is_active', true)->get()->first(function ($code) use ($enteredCode) {
            return Hash::check($enteredCode, $code->code_hash);
        });

        // Jeśli kod nie istnieje lub jest nieaktywny
        if (!$discountCode) {
            session()->flash('error', 'Kod rabatowy nie istnieje lub jest nieaktywny.');
            return redirect()->route('cart.index');
        }

        // Sprawdzamy, czy kod jest przypisany do aktualnie zalogowanego użytkownika
        if (!$discountCode->users->contains(Auth::id())) {
            session()->flash('error', 'Nieprawidłowy kod rabatowy');
            return redirect()->route('cart.index');
        }

        $cart = session()->get(
            'cart',
            []
        );
        $totalApplicable = 0;
        $nonApplicableProducts = [];

        // Obliczanie sumy dla produktów, do których kod można zastosować
        foreach ($cart as $id => $item) {
            $product = Product::find($id);
            if ($discountCode->isApplicableToProduct($product)) {
                $totalApplicable += $item['price'] * $item['quantity'];
            } else {
                $nonApplicableProducts[] = $product->name;
            }
        }

        if (
            $totalApplicable == 0
        ) {
            session()->flash('error', 'Kod rabatowy nie dotyczy żadnego z produktów w koszyku.');
            return redirect()->route('cart.index');
        }

        // Obliczenie kwoty rabatu
        $discountAmount = $discountCode->calculateDiscountAmount($totalApplicable);

        // Zapisanie informacji o kodzie rabatowym w sesji
        Session::put('discount_code', $enteredCode);
        Session::put('discount_amount', $discountAmount);
        Session::put('discount_code_id', $discountCode->id);

        if (count($nonApplicableProducts) > 0) {
            $nonApplicableList = implode(', ', $nonApplicableProducts);
            session()->flash('success', 'Kod rabatowy został częściowo zastosowany. Zniżka: ' . number_format($discountAmount, 2) . ' zł');
            session()->flash('info', 'Kod nie dotyczy niektórych produktów w koszyku: ' . $nonApplicableList);
        } else {
            session()->flash('success', 'Kod rabatowy został w pełni zastosowany. Zniżka: ' . number_format($discountAmount, 2) . ' zł');
        }

        return redirect()->route('cart.index');
    }




    private function calculateDiscountAmount(DiscountCode $discountCode)
    {
        $cart = session()->get('cart', []);
        $total = array_reduce($cart, function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);

        $discountAmount = $discountCode->type === 'fixed'
            ? $discountCode->amount
            : $total * ($discountCode->amount / 100);

        return min($discountAmount, $total);
    }


    public function calculateTotal()
    {
        $cart = session()->get('cart', []);
        $total = array_reduce($cart, function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);

        $discountAmount = session('discount_amount', 0);
        return max($total - $discountAmount, 0);
    }

    public function removeDiscount()
    {
        Session::forget('discount_code');
        Session::forget('discount_amount');
        Session::forget('discount_code_id');

        return redirect()->back()->with('success', 'Kod rabatowy został usunięty.');
    }
}
