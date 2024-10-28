<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderPickupCodeMail;
use App\Mail\OrderStatusUpdateMail;
use App\Models\DiscountCode;
use App\Models\DiscountCodeUsage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OrderController extends Controller
{
    public function create()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Twój koszyk jest pusty.');
        }

        return view('orders.create', compact('cart'));
    }

    public function store(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Twój koszyk jest pusty.');
        }


        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_address' => 'required|string',
        ]);


        $total = 0;
        foreach ($cart as $id => $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Pobranie kodu rabatowego z sesji
        $discountAmount = session('discount_amount', 0);
        $discountCodeId = session('discount_code_id', null);

        // Aktualizacja łącznej kwoty
        $total -= $discountAmount;

        // Tworzenie zamówienia
        $order = new Order();
        $order->customer_name = $request->input('customer_name');
        $order->customer_email = $request->input('customer_email');
        $order->customer_address = $request->input('customer_address');
        $order->total = $total;
        $order->status = 'W realizacji';
        $order->user_id = auth()->id();
        $order->discount_code_id = $discountCodeId;
        $order->discount_amount = $discountAmount;
        $order->save();


        foreach ($cart as $id => $item) {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $id;
            $orderItem->quantity = $item['quantity'];
            $orderItem->price = $item['price'];
            $orderItem->save();
        }

        if ($discountCodeId) {
            DiscountCodeUsage::create([
                'discount_code_id' => $discountCodeId,
                'user_id' => auth()->user()->id,
                'order_id' => $order->id,
                'discount_amount' => $discountAmount,
            ]);

            // Dezaktywacja kodu rabatowego, jeśli jest przypisany do konkretnego użytkownika
            $discountCode = DiscountCode::find($discountCodeId);
            if ($discountCode->users()->count() > 0) {
                $discountCode->is_active = false;
                $discountCode->save();
            }
        }

        // Wysłanie e-maila z potwierdzeniem zamówienia
        Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));

        // Czyszczenie koszyka i danych o kodzie rabatowym
        session()->forget('cart');
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
        $orders = Order::where('user_id', auth()->id())->with('orderItems.product')->get();
        return view('orders.my_orders', compact('orders'));
    }

    public function adminIndex()
    {
        $orders = Order::with('user', 'orderItems.product')->get();
        return view('admin.orders.index', compact('orders'));
    }

    public function update(Request $request, Order $order)
    {
        $oldStatus = $order->status;
        $newStatus = $request->input('status');

        if ($oldStatus != $newStatus) {
            $order->status = $newStatus;

            // Jeśli nowy status to "W drodze" i kod odbioru nie jest ustawiony
            if ($newStatus == 'W drodze' && !$order->pickup_code) {

                $pickupCode = strtoupper(Str::random(6));


                $order->pickup_code = $pickupCode;
            }

            $order->save();

            // Wyślij e-mail z aktualizacją statusu
            Mail::to($order->customer_email)->send(new OrderStatusUpdateMail($order));

            // Jeśli status to "W drodze", wyślij e-mail z kodem odbioru
            if ($newStatus == 'W drodze') {
                Mail::to($order->customer_email)->send(new OrderPickupCodeMail($order));
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

        if ($order->status != 'W drodze') {
            return redirect()->back()->with('error', 'Nie możesz zresetować kodu odbioru dla tego zamówienia.');
        }

        // Generowanie nowego kodu odbioru
        $pickupCode = strtoupper(Str::random(6));
        $order->pickup_code = $pickupCode;
        $order->save();

        // Wysłanie e-maila z nowym kodem odbioru
        Mail::to($order->customer_email)->send(new OrderPickupCodeMail($order));

        return redirect()->back()->with('success', 'Nowy kod odbioru został wysłany na Twój adres e-mail.');
    }
}
