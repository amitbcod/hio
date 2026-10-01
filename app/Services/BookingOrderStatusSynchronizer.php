<?php

namespace App\Services;

use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\TransportBooking;

class BookingOrderStatusSynchronizer
{
    public function markProcessingForBookingReference(int $bookingReferenceId): void
    {
        if ($bookingReferenceId <= 0) {
            return;
        }

        AccommodationBooking::where('booking_ref_id', $bookingReferenceId)
            ->update(['booking_status' => TransportBooking::STATUS_PROCESSING]);
        ActivityBooking::where('booking_ref_id', $bookingReferenceId)
            ->update(['booking_status' => TransportBooking::STATUS_PROCESSING]);
        TransportBooking::where('booking_ref_id', $bookingReferenceId)
            ->update(['booking_status' => TransportBooking::STATUS_PROCESSING]);
    }
}