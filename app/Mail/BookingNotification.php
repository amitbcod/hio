<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $audience,
        public string $bookingType,
        public string $bookingReference,
        public string $bookingName,
        public string $bookingStatus,
        public string $recipientName,
        public array $services,
        public ?array $traveller,
        public ?string $startDate,
        public ?string $endDate,
    ) {}

    public function build(): static
    {
        $typeLabel = match ($this->bookingType) {
            'accommodation' => 'Accommodation',
            'activity' => 'Activity',
            'transport' => 'Transport',
            'group' => 'Group',
            'package' => 'Package',
            default => 'Booking',
        };
        $subject = $this->audience === 'operator'
            ? 'New ' . ($this->bookingType === 'group' || $this->bookingType === 'package' ? $typeLabel . ' Service' : $typeLabel) . ' Booking – ' . $this->bookingReference
            : $typeLabel . ' Booking Confirmation – ' . $this->bookingReference;

        return $this->subject($subject)
            ->view($this->audience === 'operator'
                ? 'emails.bookings.operator-notification'
                : 'emails.bookings.traveler-confirmation');
    }
}
