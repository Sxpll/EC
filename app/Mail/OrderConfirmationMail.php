<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order; // Umożliwia dostęp do zmiennej w widoku

    /**
     * Stwórz nową instancję wiadomości.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Zbuduj wiadomość.
     */
    public function build()
    {
        return $this->subject('Potwierdzenie zamówienia')
            ->view('emails.order_confirmation');
    }
}
