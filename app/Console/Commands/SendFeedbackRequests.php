<?php

namespace App\Console\Commands;

use App\Mail\FeedbackRequest;
use App\Models\Trip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendFeedbackRequests extends Command
{
    protected $signature = 'feedback:send-requests';
    protected $description = 'Send feedback request emails for trips completed 4 days ago';

    public function handle()
    {
        $date = now()->subDays(4)->toDateString();

        $trips = Trip::where(function ($query) use ($date) {
            // For regular trips: check end_date = 4 days ago
            $query->whereDate('end_date', $date)
                // For activity trips: end_date is null, so check start_date = 4 days ago
                ->orWhere(function ($q) use ($date) {
                    $q->whereNull('end_date')
                        ->whereDate('start_date', $date);
                });
        })
            ->whereNull('feedback_request_sent_at')
            ->with('traveler')
            ->get();

        foreach ($trips as $trip) {
            if ($trip->traveler && $trip->traveler->email) {
                Mail::to($trip->traveler->email)->send(new FeedbackRequest($trip));
                $trip->feedback_request_sent_at = now();
                $trip->save();
                $this->info("Sent feedback request for trip {$trip->id}");
            }
        }

        return 0;
    }
}
