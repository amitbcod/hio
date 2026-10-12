<?php

namespace App\Services;

use App\Models\ActivityBooking;
use Illuminate\Support\Facades\Schema;

class ActivitySlotAvailabilityService
{
    public function bookedParticipantsBySlot(int $activityId, string $date, ?int $ignoreBookingId = null): array
    {
        $bookingColumns = [
            'id',
            'activity_time_slot_id',
            'participant_time_slots',
            'adults',
            'children',
        ];
        if (Schema::hasColumn('activity_bookings', 'infants')) {
            $bookingColumns[] = 'infants';
        }

        $bookings = ActivityBooking::query()
            ->where('activity_id', $activityId)
            ->whereDate('activity_date', $date)
            ->whereIn('booking_status', ['Pending', 'Confirmed'])
            ->when($ignoreBookingId, fn ($query) => $query->whereKeyNot($ignoreBookingId))
            ->get($bookingColumns);

        $bookedParticipantsBySlot = [];
        foreach ($bookings as $booking) {
            $participantSlots = $booking->participant_time_slots;
            if (is_array($participantSlots) && $participantSlots !== []) {
                foreach ($participantSlots as $slotId) {
                    if (! is_numeric($slotId) || (int) $slotId <= 0) {
                        continue;
                    }
                    $slotKey = (int) $slotId;
                    $bookedParticipantsBySlot[$slotKey] = ($bookedParticipantsBySlot[$slotKey] ?? 0) + 1;
                }
                continue;
            }

            $slotKey = (int) ($booking->activity_time_slot_id ?? 0);
            if ($slotKey > 0) {
                $bookedParticipantsBySlot[$slotKey] = ($bookedParticipantsBySlot[$slotKey] ?? 0)
                    + (int) ($booking->adults ?? 0)
                    + (int) ($booking->children ?? 0)
                    + (int) ($booking->infants ?? 0);
            }
        }

        return $bookedParticipantsBySlot;
    }
}
