<?php

namespace App\Mail;

use App\Models\TravelerAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TravelerWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public TravelerAccount $account;

    public function __construct(TravelerAccount $account)
    {
        $this->account = $account;
    }

    public function build()
    {
        $browseUrl = config('app.url') ?: url('/');
        $profileUrl = url('/traveler/profile');

        return $this->subject('Welcome to Holidays.io - Your Travel Adventure Awaits!')
            ->view('emails.traveler-welcome')
            ->with([
                'name' => $this->account->full_name,
                'email' => $this->account->email,
                'browseUrl' => $browseUrl,
                'profileUrl' => $profileUrl,
            ]);
    }
}
