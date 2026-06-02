<?php

namespace App\Mail;

use App\Models\Trip;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FeedbackReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $trip;
    public $payload;

    public function __construct(Trip $trip, array $payload = [])
    {
        $this->trip = $trip;
        $this->payload = $payload;
    }

    public function build()
    {
        return $this->subject('New feedback received for trip #' . $this->trip->id)
            ->view('emails.feedback_received')
            ->with([
                'trip' => $this->trip,
                'payload' => $this->payload,
            ]);
    }
}
