<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HLSUpdateErrorMail extends Mailable
{
    use Queueable, SerializesModels;

    private $settings;

    /**
     * Create a new message instance.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "OTS Availability update ERROR";
        $hotelId = $this->settings['hotelId'];
        $now = date('Y-m-d H:i:s');
        $errorMsg = $this->settings['message'];

        $content = <<<MAILTPLEND
<h3>An error occured during availability update from Hotel link Solutions</h3>
<p>Hotel ID: $hotelId</p>
<p>Time: $now</p>
<p>Error message: $errorMsg</p>

MAILTPLEND;


        return $this->view('emails.dynamic-template')
            ->from(env('OTS_MAIL_FROM_ADDRESS'), env('OTS_MAIL_NAME'))
            ->subject($subject)
            ->with(['content' => $content]);
    }
}
