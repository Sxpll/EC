<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $statusName;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, $statusName)
    {
        $this->order = $order;
        $this->statusName = $statusName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Aktualizacja statusu zamówienia')
            ->view('emails.order_status_update')
            ->with([
                'order' => $this->order,
                'statusName' => $this->statusName,
            ]);
    }
}
