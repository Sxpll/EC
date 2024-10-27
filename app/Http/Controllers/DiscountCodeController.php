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

    // Formularz tworzenia nowego kodu rabatowego (dla administratora)
    public function create()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Nie masz dostępu do tej strony.');
        }

        $users = User::all();
        return view('admin.discount_codes.create', compact('users'));
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
        ]);

        // Generowanie unikalnego kodu i haszowanie go
        do {
            $plainCode = Str::upper(Str::random(8));
            $codeHash = Hash::make($plainCode);
            $exists = DiscountCode::where('code_hash', $codeHash)->exists();
        } while ($exists);

        // Tworzenie nowego kodu rabatowego
        $discountCode = new DiscountCode([
            'description' => $request->input('description'),
            'amount' => $request->input('amount'),
            'type' => $request->input('type'),
            'valid_from' => $request->input('valid_from'),
            'valid_until' => $request->input('valid_until'),
            'is_active' => $request->boolean('is_active'),
            'is_single_use' => $request->boolean('is_single_use'),
            'code_hash' => $codeHash, // Zapisanie zahashowanego kodu
        ]);

        $discountCode->save();

        // Przypisanie kodu do wybranych użytkowników i wysyłanie maili
        if ($request->has('users')) {
            $discountCode->users()->attach($request->input('users'));

            $users = User::whereIn(
                'id',
                $request->input('users')
            )->get();
            foreach ($users as $user) {
                // Ustawienie flagi nowego kodu rabatowego
                $user->has_new_discount = true;
                $user->save();

                // Wysłanie e-maila z kodem rabatowym
                Mail::to($user->email)->send(new DiscountCodeMail($plainCode, $discountCode));
            }
        } else {
            $users = User::all();
            foreach ($users as $user) {
                $user->has_new_discount = true;
                $user->save();
                Mail::to($user->email)->send(new DiscountCodeMail($plainCode, $discountCode));
            }
        }

        return redirect()->route('discount_codes.index')->with('success', 'Kod rabatowy został utworzony i wysłany do użytkowników.');
    }



    // Formularz edycji kodu rabatowego (dla administratora)
    public function edit($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Nie masz dostępu do tej strony.');
        }

        $discountCode = DiscountCode::findOrFail($id);
        $users = User::all();
        return view('admin.discount_codes.edit', compact('discountCode', 'users'));
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
}
