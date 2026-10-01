<?php

namespace App\Services;

use App\Models\BookingRef;

class BookingReferenceGenerator
{
    public function generateCommon(?int $tripId): string
    {
        do {
            $reference = 'BR-'
                . ($tripId ?: 'GUEST')
                . '-' . now()->format('Ymd')
                . '-' . random_int(1, 9999);
        } while (BookingRef::where('booking_ref_code', $reference)->exists());

        return $reference;
    }
}