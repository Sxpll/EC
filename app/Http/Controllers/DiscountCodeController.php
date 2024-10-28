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

    // Zapisywanie nowego kodu rabatowego (dla administratora)
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
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        // Generowanie unikalnego kodu
        do {
            $plainCode = Str::upper(Str::random(8));
            $exists = DiscountCode::where('code', $plainCode)->exists();
        } while ($exists);

        // Tworzenie kodu rabatowego
        $discountCode = new DiscountCode([
            'description' => $request->input('description'),
            'amount' => $request->input('amount'),
            'type' => $request->input('type'),
            'valid_from' => $request->input('valid_from'),
            'valid_until' => $request->input('valid_until'),
            'is_active' => $request->boolean('is_active'),
        ]);

        // Ustawienie kodu (spowoduje to również ustawienie code_hash dzięki metodzie setCodeAttribute)
        $discountCode->code = $plainCode;

        $discountCode->save();

        // Przypisanie kodu do użytkowników
        if ($request->has('users')) {
            $discountCode->users()->attach($request->input('users'));

            // Wysyłanie e-maili do użytkowników
            $users = User::whereIn('id', $request->input('users'))->get();

            foreach ($users as $user) {
                // Wysyłanie e-maila
                Mail::to($user->email)->send(new DiscountCodeMail($plainCode, $discountCode));
            }
        } else {
            // Kod globalny, wysyłamy e-maile do wszystkich użytkowników
            $users = User::all();
            foreach ($users as $user) {
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

    // Wyświetlanie kodów rabatowych użytkownika
    public function myDiscountCodes()
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Musisz być zalogowany, aby zobaczyć swoje kody rabatowe.');
        }

        $user = Auth::user();
        $discountCodes = $user->discountCodes()->where('is_active', true)->get();

        // Pobranie historii użyć
        $usages = DiscountCodeUsage::where('user_id', $user->id)->with('discountCode', 'order')->get();

        return view('discount_codes.my_codes', compact('discountCodes', 'usages'));
    }
}
