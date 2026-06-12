<?php

namespace App\Mail;

use App\Models\GuestOtpToken;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestBookingOtp extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public object $booking,
        public GuestOtpToken $otpToken,
        public string $tripUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Booking Confirmation - Access Your Trip Details',
        );
    }

    public function content(): Content
    {
        $accommodationName = 'Your Booking';
        if (method_exists($this->booking, 'accommodation') && $this->booking->accommodation) {
            $accommodationName = $this->booking->accommodation->property_name;
        } elseif (method_exists($this->booking, 'activity') && $this->booking->activity) {
            $accommodationName = $this->booking->activity->activity_name;
        }

        $checkInDate = $this->booking->check_in_date ?? $this->booking->activity_date ?? null;
        $checkOutDate = $this->booking->check_out_date ?? $this->booking->activity_date ?? $checkInDate;

        $firstName = trim((string) data_get($this->booking, 'traveler_first_name'));
        if (empty($firstName)) {
            $guestName = trim((string) data_get($this->booking, 'guest_name'));
            if (!empty($guestName)) {
                $firstName = explode(' ', $guestName)[0];
            }
        }
        if (empty($firstName)) {
            $firstName = 'Guest';
        }

        return new Content(
            view: 'emails.guest-booking-otp',
            with: [
                'booking' => $this->booking,
                'otp' => $this->otpToken->otp_code,
                'email' => $this->otpToken->email,
                'tripUrl' => $this->tripUrl,
                'verificationUrl' => route('frontend.guest-order-search', ['email' => $this->otpToken->email]),
                'accommodationName' => $accommodationName,
                'checkInDate' => $checkInDate ? Carbon::parse($checkInDate)->format('F d, Y') : 'N/A',
                'checkOutDate' => $checkOutDate ? Carbon::parse($checkOutDate)->format('F d, Y') : 'N/A',
                'bookingRef' => $this->booking->booking_reference,
                'firstName' => $firstName,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
