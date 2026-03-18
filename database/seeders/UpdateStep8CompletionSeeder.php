<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\ActivitySchedulingTimeSlot;

class UpdateStep8CompletionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all activities that have timeslots
        $activitiesWithTimeSlots = ActivitySchedulingTimeSlot::select('activity_id')
            ->distinct()
            ->pluck('activity_id');

        // Update those activities to mark Step 8 as complete
        Activity::whereIn('id', $activitiesWithTimeSlots)
            ->update(['step8_scheduling_timeslots' => 1]);

        $this->command->info('Updated ' . $activitiesWithTimeSlots->count() . ' activities with Step 8 completion status.');
    }
}
