<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\TravelerAccount;
use Carbon\Carbon;

class TripService
{
    /**
     * Get or create Trip ID for a booking.
     * 
     * Rules:
     * - For normal bookings (no explicit trip), always create a new trip
     * - Only Add Service bookings use explicit trip IDs
     */
    public static function getOrCreateTripId(TravelerAccount $traveler, array $bookingData): int
    {
        $startDate = $bookingData['start_date'] ?? null;
        $endDate = $bookingData['end_date'] ?? null;

        $trip = Trip::create([
            'traveler_account_id' => $traveler->id,
            'title' => $bookingData['title'] ?? 'Holiday Trip',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'planned',
        ]);

        return $trip->id;
    }

    /**
     * Check if a new booking should use existing Trip ID (or create new).
     */
    public static function shouldUseExistingTrip(Trip $trip, array $newBookingData): bool
    {
        $newStart = $newBookingData['start_date'] ?? null;
        $newEnd = $newBookingData['end_date'] ?? null;

        if (!$newStart) {
            return false;
        }

        // Check if dates overlap
        return $trip->end_date >= $newStart && $trip->start_date <= ($newEnd ?? $newStart);
    }
}
