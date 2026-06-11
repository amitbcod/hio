<?php

namespace App\Services;

use App\Models\Trip;
use Carbon\Carbon;

class TripStatusService
{
    /**
     * Calculate the actual start and end dates of a trip based on all its bookings.
     * 
     * For accommodations: uses check_in_date and check_out_date
     * For activities: uses activity_date as both start and end (same day)
     * 
     * Returns array with 'start_date' and 'end_date' keys
     */
    public static function calculateTripDates(Trip $trip)
    {
        $allDates = [];

        // Collect dates from accommodation bookings
        if ($trip->accommodationBookings && $trip->accommodationBookings->isNotEmpty()) {
            foreach ($trip->accommodationBookings as $booking) {
                if ($booking->check_in_date) {
                    $allDates[] = $booking->check_in_date;
                }
                if ($booking->check_out_date) {
                    $allDates[] = $booking->check_out_date;
                }
            }
        }

        // Collect dates from activity bookings (activity_date as both start and end)
        if ($trip->activityBookings && $trip->activityBookings->isNotEmpty()) {
            foreach ($trip->activityBookings as $booking) {
                if ($booking->activity_date) {
                    $allDates[] = $booking->activity_date;
                }
            }
        }

        return [
            'start_date' => !empty($allDates) ? min($allDates) : $trip->start_date,
            'end_date' => !empty($allDates) ? max($allDates) : $trip->end_date,
        ];
    }

    /**
     * Determine the status of a trip based on current date and trip dates.
     * 
     * - 'planned' if start_date > today
     * - 'active' if start_date <= today <= end_date
     * - 'completed' if end_date < today
     * - 'cancelled' is never set by this method (manual only)
     */
    public static function determineTripStatus(Trip $trip)
    {
        $dates = self::calculateTripDates($trip);
        $startDate = Carbon::parse($dates['start_date']);
        $endDate = Carbon::parse($dates['end_date']);
        $today = Carbon::now()->startOfDay();

        if ($today < $startDate) {
            return 'planned';
        } elseif ($today >= $startDate && $today <= $endDate) {
            return 'active';
        } else {
            return 'completed';
        }
    }

    /**
     * Update trip status based on calculated dates.
     * Skips 'cancelled' status (manual only).
     */
    public static function updateTripStatus(Trip $trip)
    {
        if ($trip->status === 'cancelled') {
            // Never override manually set cancelled status
            return;
        }

        $newStatus = self::determineTripStatus($trip);
        if ($trip->status !== $newStatus) {
            $trip->update(['status' => $newStatus]);
        }
    }

    /**
     * Classify trips into "ongoing" and "past" for UI display.
     * 
     * Ongoing: status is 'planned' OR 'active'
     * Past: status is 'completed'
     */
    public static function classifyTrips($trips)
    {
        return [
            'ongoing' => $trips->filter(function ($trip) {
                return in_array($trip->status, ['planned', 'active']);
            })->values(),
            'past' => $trips->filter(function ($trip) {
                return $trip->status === 'completed';
            })->values(),
        ];
    }
}
