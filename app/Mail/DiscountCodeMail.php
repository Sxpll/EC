<?php

namespace App\Mail;

use App\Models\DiscountCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DiscountCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $discountCode;

    public function __construct($code, DiscountCode $discountCode)
    {
        $this->code = $code;
        $this->discountCode = $discountCode;
    }

    public function build()
    {
        return $this->subject('Nowy kod rabatowy')
            ->view('emails.discount_code')
            ->with([
                'code' => $this->code,
                'discountCode' => $this->discountCode,
            ]);
    }
}
