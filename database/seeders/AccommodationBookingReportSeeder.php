<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\AccommodationInventory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AccommodationBookingReportSeeder extends Seeder
{
    public function run()
    {
        $accommodations = Accommodation::with('rooms')
            ->get()
            ->filter(fn ($accommodation) => $accommodation->rooms->isNotEmpty())
            ->values();

        if ($accommodations->isEmpty()) {
            $this->command?->warn('AccommodationBookingReportSeeder skipped: no accommodations with rooms found.');
            return;
        }

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->addMonth()->endOfMonth();

        foreach ($accommodations as $accommodation) {
            $rooms = $accommodation->rooms->values();

            $cursor = $startDate->copy();
            while ($cursor->lte($endDate)) {
                foreach ($rooms as $roomIndex => $room) {
                    $sellableUnits = (int) ($room->allotment ?? $room->quantity ?? 0);
                    if ($sellableUnits <= 0) {
                        $sellableUnits = 4;
                    }

                    $weekendMultiplier = in_array($cursor->dayOfWeek, [5, 6], true) ? 0.55 : 0.30;
                    $seedSoldUnits = (int) floor($sellableUnits * $weekendMultiplier);

                    $isBlocked = ($cursor->day % 11 === 0) && ($roomIndex === 0);
                    if ($isBlocked) {
                        $seedSoldUnits = $sellableUnits;
                    }

                    AccommodationInventory::updateOrCreate(
                        [
                            'accommodation_id' => $accommodation->id,
                            'room_id' => $room->id,
                            'date' => $cursor->toDateString(),
                        ],
                        [
                            'sellable_units' => $sellableUnits,
                            'sold_units' => $seedSoldUnits,
                            'available_units' => max($sellableUnits - $seedSoldUnits, 0),
                            'stop_sell' => $isBlocked,
                            'is_blocked' => $isBlocked,
                            'sell_and_report' => true,
                            'block_arrivals' => false,
                            'instant_on_request' => 'Instant',
                        ]
                    );
                }

                $cursor->addDay();
            }

            for ($index = 1; $index <= 24; $index++) {
                $room = $rooms[($index - 1) % $rooms->count()];

                $checkIn = $startDate->copy()->addDays(($index * 2) % 45);
                $nights = ($index % 4) + 1;
                $checkOut = $checkIn->copy()->addDays($nights);
                $roomsBooked = ($index % 5 === 0) ? 2 : 1;

                $status = 'Confirmed';
                if ($index % 7 === 0) {
                    $status = 'Pending';
                }
                if ($index % 11 === 0) {
                    $status = 'Cancelled';
                }

                $dailyRate = 80 + ($index * 7);
                $totalAmount = round($dailyRate * $nights * $roomsBooked, 2);

                AccommodationBooking::updateOrCreate(
                    [
                        'booking_reference' => sprintf('BR-ACC-%d-%03d', $accommodation->id, $index),
                    ],
                    [
                        'accommodation_id' => $accommodation->id,
                        'room_id' => $room->id,
                        'guest_name' => 'Demo Guest ' . $index,
                        'guest_email' => 'demo.guest' . $index . '@example.test',
                        'check_in_date' => $checkIn->toDateString(),
                        'check_out_date' => $checkOut->toDateString(),
                        'rooms_booked' => $roomsBooked,
                        'adults' => 2,
                        'children' => $index % 3 === 0 ? 1 : 0,
                        'booking_status' => $status,
                        'total_amount' => $totalAmount,
                        'currency' => 'USD',
                        'source_channel' => $index % 2 === 0 ? 'Direct' : 'OTA',
                        'booked_at' => $checkIn->copy()->subDays(($index % 6) + 1),
                    ]
                );
            }
        }

        $this->command?->info('Accommodation booking report demo data seeded successfully.');
    }
}
