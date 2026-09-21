<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = ['traveler_account_id', 'title', 'start_date', 'end_date', 'status'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function traveler()
    {
        return $this->belongsTo(TravelerAccount::class, 'traveler_account_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function accommodationBookings()
    {
        return $this->hasMany(AccommodationBooking::class, 'trip_id');
    }

    public function activityBookings()
    {
        return $this->hasMany(ActivityBooking::class, 'trip_id');
    }

    public function transportBookings()
    {
        return $this->hasMany(TransportBooking::class, 'trip_id');
    }

    public function travellers()
    {
        return $this->hasMany(Traveller::class);
    }

    public function bookingRefs()
    {
        return $this->hasMany(\App\Models\BookingRef::class, 'trip_id');
    }

    public function getBookingReferencesAttribute(): array
    {
        $refs = [];

        foreach (['accommodationBookings', 'activityBookings', 'transportBookings'] as $relationName) {
            $items = $this->relationLoaded($relationName) ? $this->getRelation($relationName) : $this->{$relationName}()->get();

            foreach ($items as $item) {
                $ref = $item->booking_reference ?? null;
                if (is_string($ref) && trim($ref) !== '' && !in_array($ref, $refs, true)) {
                    $refs[] = $ref;
                }
            }
        }

        // include package-style booking line item refs
        foreach ($this->bookings as $booking) {
            foreach ($booking->lineItems ?? collect() as $lineItem) {
                if (($lineItem->service_type ?? null) !== 'package') {
                    continue;
                }

                $ref = 'PACKAGE-' . ($lineItem->service_id ?? $lineItem->id ?? $booking->id);
                if (!in_array($ref, $refs, true)) {
                    $refs[] = $ref;
                }
            }
        }

        // include booking_ref parent codes (per-transaction refs)
        try {
            $brs = $this->relationLoaded('bookingRefs') ? $this->getRelation('bookingRefs') : $this->bookingRefs()->get();
            foreach ($brs as $b) {
                $code = $b->booking_ref_code ?? null;
                if (is_string($code) && trim($code) !== '' && !in_array($code, $refs, true)) {
                    $refs[] = $code;
                }
            }
        } catch (\Exception $e) {
            // ignore if booking_refs table/relations not present in older installs
        }

        return array_values($refs);
    }

    public function getPrimaryBookingReferenceAttribute(): ?string
    {
        return $this->booking_references[0] ?? null;
    }
}
