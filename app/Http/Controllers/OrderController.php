<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;

class OrderController extends Controller
{
    public function create()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        return view('orders.create', compact('cart'));
    }

    public function store(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Twój koszyk jest pusty.');
        }

        // Walidacja danych
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_address' => 'required|string',
        ]);

        // Oblicz łączną kwotę
        $total = 0;
        foreach ($cart as $id => $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Tworzenie zamówienia
        $order = new Order();
        $order->customer_name = $request->input('customer_name');
        $order->customer_email = $request->input('customer_email');
        $order->customer_address = $request->input('customer_address');
        $order->total = $total;
        $order->status = 'pending';
        $order->user_id = auth()->id();

        $order->save();

        // Dodawanie pozycji zamówienia
        foreach ($cart as $id => $item) {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $id;
            $orderItem->quantity = $item['quantity'];
            $orderItem->price = $item['price'];
            $orderItem->save();
        }

        // Wyczyść koszyk
        session()->forget('cart');

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
        $order->status = $request->input('status');
        $order->save();

        return redirect()->route('admin.orders')->with('success', 'Order status updated successfully.');
    }
}
