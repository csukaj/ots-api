<?php

namespace App\Mail;

use App\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderSent extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from(env('OTS_MAIL_FROM_ADDRESS'), env('OTS_MAIL_NAME'))
            ->bcc(env('OTS_MAIL_FROM_ADDRESS'))
            ->replyTo(env('OTS_REPLY_TO_ADDRESS'))
            ->subject('OTS Order details')
            ->view('emails.userorder');
    }
}
