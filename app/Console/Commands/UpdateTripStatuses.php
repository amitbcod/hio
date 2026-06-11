<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Services\TripStatusService;
use Illuminate\Console\Command;

class UpdateTripStatuses extends Command
{
    protected $signature = 'trips:update-statuses';
    protected $description = 'Update trip statuses based on current date and booking dates';

    public function handle()
    {
        $this->info('Starting trip status update...');

        // Get all trips that are not manually cancelled
        $trips = Trip::whereIn('status', ['planned', 'active', 'completed'])->get();

        $updated = 0;
        foreach ($trips as $trip) {
            // Reload relationships to ensure fresh data
            $trip->load(['accommodationBookings', 'activityBookings']);

            $oldStatus = $trip->status;
            TripStatusService::updateTripStatus($trip);

            if ($trip->status !== $oldStatus) {
                $this->line("Trip #{$trip->id}: {$oldStatus} → {$trip->status}");
                $updated++;
            }
        }

        $this->info("Trip status update complete. {$updated} trips updated.");
        return 0;
    }
}
