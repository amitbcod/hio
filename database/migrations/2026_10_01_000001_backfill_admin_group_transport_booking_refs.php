<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transport_bookings')
            || !Schema::hasColumn('transport_bookings', 'trip_id')
            || !Schema::hasColumn('transport_bookings', 'booking_ref_id')
            || !Schema::hasTable('bookings')
            || !Schema::hasColumn('bookings', 'booking_type')
            || !Schema::hasColumn('bookings', 'booking_ref_id')) {
            return;
        }

        $tripIds = DB::table('transport_bookings')
            ->whereNull('booking_ref_id')
            ->whereNotNull('trip_id')
            ->distinct()
            ->pluck('trip_id');

        if ($tripIds->isEmpty()) {
            return;
        }

        $referencesByTrip = DB::table('bookings')
            ->whereIn('trip_id', $tripIds)
            ->where('booking_type', 'open-group')
            ->whereNotNull('booking_ref_id')
            ->get(['trip_id', 'booking_ref_id'])
            ->groupBy('trip_id');

        foreach ($referencesByTrip as $tripId => $references) {
            $referenceIds = $references->pluck('booking_ref_id')->unique();
            if ($referenceIds->count() !== 1) {
                continue;
            }

            DB::table('transport_bookings')
                ->where('trip_id', $tripId)
                ->whereNull('booking_ref_id')
                ->update(['booking_ref_id' => $referenceIds->first()]);
        }
    }

    public function down(): void
    {
        // Keep backfilled links intact; removing them could discard valid booking references.
    }
};