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
     * - Same Trip if dates overlap/match for same traveller
     * - Same Trip also if new booking falls within 15 days after an existing trip's end date
     * - New Trip only if date window does not match or the previous trip expired by more than 15 days
     */
    public static function getOrCreateTripId(TravelerAccount $traveler, array $bookingData): int
    {
        $startDate = $bookingData['start_date'] ?? null;
        $endDate = $bookingData['end_date'] ?? null;
        $today = Carbon::now()->startOfDay();
        $tripExpiryThreshold = $today->copy()->subDays(15);

        $existingTrip = Trip::where('traveler_account_id', $traveler->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($tripExpiryThreshold) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $tripExpiryThreshold->toDateString());
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
                })
                ->orWhere(function ($q) use ($startDate) {
                    $q->whereNotNull('end_date')
                      ->where('end_date', '<', $startDate)
                      ->where('end_date', '>=', Carbon::parse($startDate)->subDays(15)->toDateString());
                });
            })
            ->when($startDate && !$endDate, function ($query) use ($startDate) {
                $query->where(function ($q) use ($startDate) {
                    $q->where('start_date', '<=', $startDate)
                      ->where(function ($q2) use ($startDate) {
                          $q2->where('end_date', '>=', $startDate)
                             ->orWhereNull('end_date');
                      });
                })
                ->orWhere(function ($q) use ($startDate) {
                    $q->whereNotNull('end_date')
                      ->where('end_date', '<', $startDate)
                      ->where('end_date', '>=', Carbon::parse($startDate)->subDays(15)->toDateString());
                });
            })
            ->orderByDesc('end_date')
            ->orderByDesc('created_at')
            ->first();

        if ($existingTrip) {
            if ($startDate && (!$existingTrip->start_date || $startDate < $existingTrip->start_date)) {
                $existingTrip->update(['start_date' => $startDate]);
            }
            if ($endDate && (!$existingTrip->end_date || $endDate > $existingTrip->end_date)) {
                $existingTrip->update(['end_date' => $endDate]);
            }

            return $existingTrip->id;
        }

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
