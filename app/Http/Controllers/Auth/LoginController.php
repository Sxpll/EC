<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\CartService;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
        $this->middleware('guest')->except('logout');
    }

    protected function credentials(Request $request)
    {
        return array_merge($request->only($this->username(), 'password'), ['isActive' => true]);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $errors = [$this->username() => trans('auth.failed')];

        // Custom error message if the account is inactive
        $user = \App\Models\User::where($this->username(), $request->{$this->username()})->first();

        if ($user && !$user->isActive) {
            $errors = [$this->username() => 'Your account is not active.'];
        }

        if ($request->expectsJson()) {
            return response()->json($errors, 422);
        }

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors($errors);
    }

    protected function authenticated(Request $request, $user)
    {
        $cartService = app(CartService::class);

        $cookieCart = $cartService->getCartFromCookies();
        $databaseCart = $cartService->getCartFromDatabase();

        if (!empty($cookieCart) && !empty($databaseCart)) {
            // Przechowaj koszyki w sesji, aby przekazać je do widoku
            session([
                'cookieCart' => $cookieCart,
                'databaseCart' => $databaseCart,
            ]);

            // Przekieruj do widoku z opcjami wyboru
            return redirect()->route('cart.mergeOptions');
        } elseif (!empty($cookieCart) && empty($databaseCart)) {
            // Koszyk w bazie danych jest pusty - przenieś koszyk z ciasteczek
            $cartService->useCookieCart();
        } elseif (empty($cookieCart) && !empty($databaseCart)) {
            // Koszyk w ciasteczkach jest pusty - nic nie robimy
        }
        // Jeśli oba koszyki są puste - nic nie robimy

        // Kontynuuj normalne przetwarzanie
        return redirect()->intended($this->redirectPath());
    }


    private function cartsAreDifferent($cart1, $cart2)
    {
        return json_encode($cart1) !== json_encode($cart2);
    }
}
