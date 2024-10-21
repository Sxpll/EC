<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPickupCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order; // Allows access to the order in the email view

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Zamówienie w drodze - Kod odbioru')
            ->view('emails.order_pickup_code');
    }
}
