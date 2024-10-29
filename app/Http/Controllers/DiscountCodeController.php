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
use App\Services\CartService;



class DiscountCodeController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->middleware('auth');
        $this->cartService = $cartService;
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
            'categories' => 'nullable',
        ]);

        $users = $request->has('users') ? User::whereIn('id', $request->input('users'))->get() : User::all();

        foreach ($users as $user) {
            $attempts = 0;
            $maxAttempts = 100; //tu dalem 100 nie wiem czy nie za duzo

            do {
                $plainCode = Str::upper(Str::random(10)); // Zmienilem na 10 znakow zeby zmniejszyc ryzyko wyczerpania
                $codeHash = Hash::make($plainCode);
                $exists = DiscountCode::where('code_hash', $codeHash)->exists();
                $attempts++;

                if ($attempts >= $maxAttempts) {
                    throw new \Exception("Nie udało się wygenerować unikalnego kodu po $maxAttempts probach.");
                }
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

            if ($request->has('categories')) {
                $categories = json_decode($request->input('categories'), true);
                foreach ($categories as $categoryId) {
                    $discountCode->categories()->attach($categoryId);
                }
            }

            $discountCode->users()->attach($user->id);
            $user->has_new_discount = true;
            $user->save();

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
        $discountCode = DiscountCode::where('is_active', true)->get()->first(function ($code) use ($enteredCode) {
            return Hash::check($enteredCode, $code->code_hash);
        });

        if (!$discountCode) {
            session()->flash('error', 'Kod rabatowy nie istnieje lub jest nieaktywny.');
            return redirect()->route('cart.index');
        }

        if (!$discountCode->users->contains(Auth::id())) {
            session()->flash('error', 'Nieprawidłowy kod rabatowy');
            return redirect()->route('cart.index');
        }

        $totalApplicable = $this->cartService->calculateTotal();
        $discountAmount = $discountCode->calculateDiscountAmount($totalApplicable);

        session()->put('discount_code', $enteredCode);
        session()->put('discount_amount', $discountAmount);
        session()->put('discount_code_id', $discountCode->id);

        session()->flash('success', 'Kod rabatowy został zastosowany.');
        return redirect()->route('cart.index');
    }






    public function calculateTotal()
    {
        $discountAmount = session('discount_amount', 0);
        return $this->cartService->calculateTotal($discountAmount);
    }

    public function removeDiscount()
    {
        Session::forget('discount_code');
        Session::forget('discount_amount');
        Session::forget('discount_code_id');

        return redirect()->back()->with('success', 'Kod rabatowy został usunięty.');
    }
}
