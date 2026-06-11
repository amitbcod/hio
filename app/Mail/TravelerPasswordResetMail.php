<?php

namespace App\Mail;

use App\Models\TravelerAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TravelerPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public TravelerAccount $account;
    public string $token;
    public string $resetUrl;

    public function __construct(TravelerAccount $account, string $token)
    {
        $this->account = $account;
        $this->token = $token;
        $this->resetUrl = route('traveler.password.reset', $token) . '?email=' . urlencode($account->email);
    }

    public function build()
    {
        return $this->subject('Reset your Holidays.io password')
            ->view('emails.traveler-password-reset')
            ->with([
                'name' => $this->account->full_name ?: 'Traveler',
                'resetUrl' => $this->resetUrl,
            ]);
    }
}
