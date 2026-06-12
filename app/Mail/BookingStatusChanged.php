<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

class BookingStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public Model $booking;
    public string $bookingType;
    public string $status;
    public string $recipientType;

    public function __construct(Model $booking, string $bookingType, string $status, string $recipientType)
    {
        $this->booking = $booking;
        $this->bookingType = $bookingType;
        $this->status = $status;
        $this->recipientType = $recipientType;
    }

    public function build()
    {
        $serviceName = $this->getServiceName();
        $bookingReference = $this->booking->booking_reference ?? 'N/A';
        $statusLabel = $this->status === 'Confirmed' ? 'confirmed' : 'cancelled';

        $subject = sprintf('HIO: Booking %s - %s', $statusLabel, $serviceName);

        return $this->subject($subject)
            ->view('emails.booking_status_changed')
            ->with([
                'bookingReference' => $bookingReference,
                'serviceType' => ucfirst($this->bookingType),
                'serviceName' => $serviceName,
                'status' => $this->status,
                'statusLabel' => $statusLabel,
                'bookingDate' => $this->booking->created_at?->format('M d, Y H:i') ?? null,
                'bookingAmount' => $this->booking->total_amount ?? null,
                'bookingCurrency' => $this->booking->currency ?? null,
                'guestEmail' => $this->booking->guest_email ?? null,
                'recipientType' => $this->recipientType,
            ]);
    }

    private function getServiceName(): string
    {
        if ($this->bookingType === 'activity') {
            return optional($this->booking->activity)->activity_name
                ?? optional($this->booking->activity)->title
                ?? 'Activity Booking';
        }

        if ($this->bookingType === 'accommodation') {
            return optional($this->booking->accommodation)->accommodation_name
                ?? optional($this->booking->accommodation)->name
                ?? 'Accommodation Booking';
        }

        return 'Booking';
    }
}
