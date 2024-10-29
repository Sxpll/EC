<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\CartService;
use App\Http\Controllers\DiscountCodeController;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart = $this->cartService->getCart();
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

        $this->cartService->addProductToCart($product);

        return redirect()->back()->with('success', 'Produkt został dodany do koszyka.');
    }

    public function update(Request $request, $id)
    {
        $item = $this->cartService->updateProductQuantity($id, $request->quantity);

        if ($item) {
            $total = app(DiscountCodeController::class)->calculateTotal();
            return response()->json([
                'success' => true,
                'item' => $item,
                'total_formatted' => number_format($total, 2) . ' zł',
            ]);
        }

        return response()->json(['success' => false]);
    }

    public function remove(Request $request, $id)
    {
        $this->cartService->removeProductFromCart($id);
        return back()->with('success', 'Produkt został usunięty z koszyka!');
    }

    public function clear()
    {
        $this->cartService->clearCart();
        return back()->with('success', 'Koszyk został wyczyszczony!');
    }

    public function contents()
    {
        $total = app(DiscountCodeController::class)->calculateTotal();
        $cart = $this->cartService->getCart();

        return response()->json([
            'cart' => $cart,
            'total' => $total,
        ]);
    }
}
