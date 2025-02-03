<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderPickupCodeMail;
use App\Mail\OrderStatusUpdateMail;
use App\Models\DiscountCode;
use App\Models\DiscountCodeUsage;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class OrderController extends Controller
{

    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService; // Wstrzyknięcie usługi koszyka
    }

    public function create()
    {
        $cart = $this->cartService->getCart(); // Użycie CartService do pobrania koszyka
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Twój koszyk jest pusty.');
        }

        return view('orders.create', compact('cart'));
    }

    public function store(Request $request)
    {
        $cart = $this->cartService->getCart(); // Użycie CartService do pobrania koszyka
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Twój koszyk jest pusty.');
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_address' => 'required|string',
        ]);

        $total = array_reduce($cart, function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);

        $discountAmount = session('discount_amount', 0);
        $discountCodeId = session('discount_code_id', null);
        $total -= $discountAmount;

        $order = new Order();
        $order->customer_name = $request->input('customer_name');
        $order->customer_email = $request->input('customer_email');
        $order->customer_address = $request->input('customer_address');
        $order->total = $total;

        if (Auth::check()) {
            $order->user_id = auth()->id();
        }

        $order->discount_code_id = $discountCodeId;
        $order->discount_amount = $discountAmount;

        $statusInProgress = OrderStatus::where('code', 'in_progress')->first();
        $order->status_id = $statusInProgress->id;
        $order->save();

        foreach ($cart as $id => $item) {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $id;
            $orderItem->quantity = $item['quantity'];
            $orderItem->price = $item['price'];
            $orderItem->save();
        }

        // Wysyłanie e-maila z użyciem Mailtrap
        Mail::send('emails.order_confirmation', ['order' => $order], function ($message) use ($order) {
            $message->to($order->customer_email)
                ->subject('Potwierdzenie zamówienia');
        });


        // Czyszczenie koszyka i sesji z kodem rabatowym
        $this->cartService->clearCart();
        session()->forget('discount_code');
        session()->forget('discount_amount');
        session()->forget('discount_code_id');

        return redirect()->route('orders.thankyou')->with('success', 'Zamówienie zostało złożone pomyślnie!');
    }


    public function thankyou()
    {
        return view('orders.thankyou');
    }
    public function myOrders()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['orderItems.product', 'discountCode', 'status'])
            ->get();

        return view('orders.my_orders', compact('orders'));
    }

    public function adminIndex()
    {

        $orders = Order::with(['user', 'orderItems.product', 'status'])->get();

        $statuses = OrderStatus::all();


        return view('admin.orders.index', compact('orders', 'statuses'));
    }






    public function update(Request $request, Order $order)
    {
        $oldStatus = $order->status_id;
        $newStatusId = $request->input('status_id');

        if ($oldStatus != $newStatusId) {
            $order->status_id = $newStatusId;

            $statusOnTheWay = OrderStatus::where('code', 'on_the_way')->first();
            if ($newStatusId == $statusOnTheWay->id && !$order->pickup_code) {
                $pickupCode = strtoupper(Str::random(6));
                $order->pickup_code = $pickupCode;
            }

            $order->save();

            $newStatusName = $order->status->name;

            Mail::send('emails.order_status_update', ['order' => $order, 'statusName' => $newStatusName], function ($message) use ($order) {
                $message->to($order->customer_email)
                    ->subject('Aktualizacja statusu zamówienia');
            });

            if ($newStatusId == $statusOnTheWay->id) {
                // Wysłanie kodu odbioru
                Mail::send('emails.order_pickup_code', ['order' => $order], function ($message) use ($order) {
                    $message->to($order->customer_email)
                        ->subject('Zamówienie w drodze - Kod odbioru');
                });
            }

            return redirect()->route('admin.orders')->with('success', 'Status zamówienia został zaktualizowany.');
        }

        return redirect()->route('admin.orders')->with('info', 'Status zamówienia pozostał bez zmian.');
    }



    public function resetPickupCode(Request $request, $orderId)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Musisz być zalogowany, aby zresetować kod odbioru.');
        }

        $order = Order::where('id', $orderId)->where('user_id', Auth::id())->firstOrFail();

        $statusOnTheWay = OrderStatus::where('code', 'on_the_way')->first();
        if ($order->status_id != $statusOnTheWay->id) {
            return redirect()->back()->with('error', 'Nie możesz zresetować kodu odbioru dla tego zamówienia.');
        }

        $pickupCode = strtoupper(Str::random(6));
        $order->pickup_code = $pickupCode;
        $order->save();

        $subject = 'Zamówienie w drodze - Nowy kod odbioru';
        $body = view('emails.order_pickup_code', ['order' => $order])->render();

        // Wysyłanie e-maila
        \App\Services\PHPMailerService::sendMail($order->customer_email, $subject, $body);

        return redirect()->back()->with('success', 'Nowy kod odbioru został wysłany na Twój adres e-mail.');
    }
}
