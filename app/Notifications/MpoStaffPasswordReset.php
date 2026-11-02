<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MpoStaffPasswordReset extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset your MPO staff password')
            ->line('A password reset was requested for your MPO staff account.')
            ->action('Reset Password', route('mpo.users.password.reset.form', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]))
            ->line('This link expires in 60 minutes. If you did not request this, you can ignore this email.');
    }
}
