<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a notification instance.
     *
     * @param  string $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $userCreated = new Carbon($notifiable->created_at);
        $now = Carbon::now();
        if (abs($now->diffInSeconds($userCreated)) < 10) {
            //if user just created, send him welcome message
            return (new MailMessage)
                ->subject('Complete your OTS Group Extranet registration')
                ->greeting("Dear {$notifiable->name},")
                ->line('Please use the below button to set your password and access the OTS Group Extranet.')
                ->action('Set my password',
                    url(env('EXTRANET_URL',
                            config('app.url')) . env('EXTRANET_PASSWORD_RESET_PATH') . '?' . $this->token))
                ->line('If you have not requested a password, please ignore this email.');
        } else {
            //else send him password change message
            return (new MailMessage)
                ->subject('Password change request')
                ->greeting("Dear {$notifiable->name},")
                ->line('Please use the below button to set a new password and access the OTS Group Extranet. ')
                ->action('Change my password',
                    url(env('EXTRANET_URL',
                            config('app.url')) . env('EXTRANET_PASSWORD_RESET_PATH') . '?' . $this->token))
                ->line('If you did not request for your password to be reset, please ignore this email. Your password will not be changed.');
        }
    }
}
