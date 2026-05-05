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

        return new Content(
            view: 'emails.guest-booking-otp',
            with: [
                'booking' => $this->booking,
                'otp' => $this->otpToken->otp_code,
                'email' => $this->otpToken->email,
                'tripUrl' => $this->tripUrl,
                'accommodationName' => $accommodationName,
                'checkInDate' => $checkInDate ? Carbon::parse($checkInDate)->format('F d, Y') : 'N/A',
                'checkOutDate' => $checkOutDate ? Carbon::parse($checkOutDate)->format('F d, Y') : 'N/A',
                'bookingRef' => $this->booking->booking_reference,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
