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

        $trips = Trip::whereDate('end_date', $date)
            ->whereNull('feedback_request_sent_at')
            ->where('status', 'Completed')
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
