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
        $payload = $this->payload;

        // Enrich accommodation entries with property name when possible
        if (!empty($payload['accommodations'])) {
            foreach ($payload['accommodations'] as $abKey => &$acc) {
                $id = $acc['id'] ?? $abKey;
                $name = null;

                if ($this->trip && isset($this->trip->accommodationBookings)) {
                    $booking = $this->trip->accommodationBookings->firstWhere('id', $id);
                    if ($booking) {
                        $name = optional($booking->accommodation)->property_name ?? null;
                    }
                }

                if (!$name) {
                    $booking = \App\Models\AccommodationBooking::find($id);
                    if ($booking) {
                        $name = optional($booking->accommodation)->property_name;
                    }
                }

                if (!$name) {
                    $model = \App\Models\Accommodation::find($id);
                    if ($model) {
                        $name = $model->property_name ?? null;
                    }
                }

                $acc['name'] = $name ?? ('Accommodation #' . $id);
            }
            unset($acc);
        }

        // Enrich activity entries with activity name when possible
        if (!empty($payload['activities'])) {
            foreach ($payload['activities'] as $actKey => &$act) {
                $id = $act['id'] ?? $actKey;
                $name = null;

                if ($this->trip && isset($this->trip->activityBookings)) {
                    $booking = $this->trip->activityBookings->firstWhere('id', $id);
                    if ($booking) {
                        $name = optional($booking->activity)->activity_name ?? null;
                    }
                }

                if (!$name) {
                    $booking = \App\Models\ActivityBooking::find($id);
                    if ($booking) {
                        $name = optional($booking->activity)->activity_name;
                    }
                }

                if (!$name) {
                    $model = \App\Models\Activity::find($id);
                    if ($model) {
                        $name = $model->activity_name ?? null;
                    }
                }

                $act['name'] = $name ?? ('Activity #' . $id);
            }
            unset($act);
        }

        $this->payload = $payload;

        return $this->subject('New feedback received for trip #' . $this->trip->id)
            ->view('emails.feedback_received')
            ->with([
                'trip' => $this->trip,
                'payload' => $this->payload,
            ]);
    }
}
