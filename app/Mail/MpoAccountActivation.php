<?php

namespace App\Mail;

use App\Models\AdminUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MpoAccountActivation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AdminUser $administrator,
        public string $activationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Holidays.io MPO account');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mpo-account-activation',
            with: [
                'administrator' => $this->administrator,
                'activationUrl' => $this->activationUrl,
            ],
        );
    }
}
