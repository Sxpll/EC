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
        $categories = $this->getJsTreeCategories(); // Pobranie danych w formacie JSON do `jstree`
        $discountCode = new DiscountCode();

        return view('admin.discount_codes.create', compact('users', 'categories', 'discountCode'));
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
            'categories' => 'nullable|array',  // upewnij się, że jest tablicą
            'categories.*' => 'exists:categories,id',
        ]);

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
            'is_single_use' => $request->boolean('is_single_use'),
            'code_hash' => $codeHash,
        ]);

        $discountCode->save();

        if ($request->has('categories')) {
            // Konwersja na tablicę, aby upewnić się, że są pojedyncze wartości ID
            $categories = array_map('intval', (array) $request->input('categories'));
            $discountCode->categories()->attach($categories);
        }


        if ($request->has('users')) {
            $discountCode->users()->attach($request->input('users'));
            $users = User::whereIn(
                'id',
                $request->input('users')
            )->get();
            foreach ($users as $user) {
                $user->has_new_discount = true;
                $user->save();
                Mail::to($user->email)->send(new DiscountCodeMail($plainCode, $discountCode));
            }
        }

        return redirect()->route('discount_codes.index')->with('success', 'Kod rabatowy został utworzony i wysłany do użytkowników.');
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

    private function calculateDiscountedPrice($price, DiscountCode $discountCode)
    {
        if ($discountCode->type === 'fixed') {
            return max(0, $price - $discountCode->amount);
        } elseif ($discountCode->type === 'percentage') {
            return max(0, $price * (1 - $discountCode->amount / 100));
        }

        return $price;
    }

    public function applyDiscountCode(Request $request, $productId)
    {
        $discountCode = DiscountCode::where('code', $request->input('code'))->first();

        if (!$discountCode) {
            return response()->json(['error' => 'Kod rabatowy nie istnieje.'], 404);
        }

        // Pobierz produkt
        $product = Product::findOrFail($productId);

        // Sprawdź, czy kod rabatowy można zastosować do tego produktu
        if (!$discountCode->isApplicableToProduct($product)) {
            return response()->json(['error' => 'Kod rabatowy nie dotyczy tego produktu.'], 400);
        }

        // Zastosuj rabat
        $discountedPrice = $this->calculateDiscountedPrice($product->price, $discountCode);
        return response()->json(['discounted_price' => $discountedPrice]);
    }
}
