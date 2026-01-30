<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminOwnerClaimedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $verification;

    public function __construct($verification)
    {
        $this->verification = $verification;
    }

    public function build()
    {
        return $this->subject('HIO Admin: Owner claimed business — action required')
                    ->view('emails.admin_owner_claimed_notification')
                    ->with(['verification' => $this->verification]);
    }
}