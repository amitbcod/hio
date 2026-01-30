<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerClaimedToRequester extends Mailable
{
    use Queueable, SerializesModels;

    public $verification;

    public function __construct($verification)
    {
        $this->verification = $verification;
    }

    public function build()
    {
        return $this->subject('HIO: Owner has claimed the business')
                    ->view('emails.owner_claimed_to_requester')
                    ->with(['verification' => $this->verification]);
    }
}