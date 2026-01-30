<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ControllerVerificationRequested extends Mailable
{
    use Queueable, SerializesModels;

    public $verification;

    public function __construct($verification)
    {
        $this->verification = $verification;
    }

    public function build()
    {
        $token = $this->verification->token;
        $url = url("/operator/register/controller/verify/{$token}");

        return $this->subject('HIO: Owner verification requested')
                    ->view('emails.controller_verification_requested')
                    ->with(['url' => $url, 'verification' => $this->verification]);
    }
}