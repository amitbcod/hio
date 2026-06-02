<?php

namespace App\Mail;

use App\Models\Trip;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FeedbackRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $trip;

    public function __construct(Trip $trip)
    {
        $this->trip = $trip;
    }

    public function build()
    {
        $url = route('frontend.feedback.show', [$this->trip->id]);

        return $this->subject('Please review your trip')
            ->view('emails.feedback_request')
            ->with([
                'trip' => $this->trip,
                'url' => $url,
            ]);
    }
}
