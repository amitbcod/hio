<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\FeedbackReceived;
use App\Mail\FeedbackRequest as FeedbackRequestMail;
use App\Models\Trip;
use App\Models\Review;
use App\Models\ReviewItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    public function show($tripId)
    {
        $trip = Trip::with(['traveler', 'accommodationBookings.accommodation', 'activityBookings.activity'])->findOrFail($tripId);
        
        // Check if user is authenticated and owns this trip
        $traveler = auth('traveler')->user();
        if (!$traveler || $trip->traveler_account_id !== $traveler->id) {
            abort(403, 'You can only view feedback for your own trips.');
        }
        
        // Check if a review already exists for this trip
        $review = Review::with('items')->where('trip_id', $tripId)->first();

        $tripReviewItem = null;
        $accommodationReviews = collect();
        $activityReviews = collect();

        if ($review) {
            $tripReviewItem = $review->items->firstWhere('service_type', 'trip');
            $accommodationReviews = $review->items->where('service_type', 'accommodation')->keyBy('service_id');
            $activityReviews = $review->items->where('service_type', 'activity')->keyBy('service_id');
        }

        return view('frontend.feedback', compact('trip', 'review', 'tripReviewItem', 'accommodationReviews', 'activityReviews'));
    }

    public function sendRequest($tripId)
    {
        $trip = Trip::with('traveler')->findOrFail($tripId);
        $traveler = $trip->traveler;

        if (!$traveler || empty($traveler->email)) {
            return back()->with('error', 'Traveler email not found.');
        }

        Mail::to($traveler->email)->send(new FeedbackRequestMail($trip));

        return back()->with('success', 'Feedback email sent to traveler.');
    }

    public function submit(Request $request, $tripId)
    {
        $trip = Trip::with(['traveler', 'accommodationBookings.accommodation', 'activityBookings.activity'])->findOrFail($tripId);
        
        // Check if user is authenticated and owns this trip
        $traveler = auth('traveler')->user();
        if (!$traveler || $trip->traveler_account_id !== $traveler->id) {
            abort(403, 'You can only submit feedback for your own trips.');
        }

        $payload = $request->all();

        // Check if review already exists
        $review = Review::where('trip_id', $trip->id)->first();
        
        if ($review) {
            // Update existing review
            $review->update([
                'overall_rating' => $payload['overall_rating'] ?? null,
                'overall_review' => json_encode([
                    'hear_about_us' => $payload['hear_about_us'] ?? null,
                    'trip_comments' => $payload['trip_comments'] ?? null,
                ]),
            ]);
            
            // Delete existing review items and recreate them
            ReviewItem::where('review_id', $review->id)->delete();
        } else {
            // Create new review
            $review = Review::create([
                'trip_id' => $trip->id,
                'traveler_account_id' => $trip->traveler_account_id ?? null,
                'overall_rating' => $payload['overall_rating'] ?? null,
                'overall_review' => json_encode([
                    'hear_about_us' => $payload['hear_about_us'] ?? null,
                    'trip_comments' => $payload['trip_comments'] ?? null,
                ]),
            ]);
        }

        // Trip-level ratings
        $tripCriteria = [
            'communication' => $payload['trip']['communication'] ?? null,
            'booking_service' => $payload['trip']['booking_service'] ?? null,
            'travel_consulting' => $payload['trip']['travel_consulting'] ?? null,
            'on_destination' => $payload['trip']['on_destination'] ?? null,
            'post_booking' => $payload['trip']['post_booking'] ?? null,
        ];

        ReviewItem::create([
            'review_id' => $review->id,
            'service_type' => 'trip',
            'service_id' => 0,
            'criteria' => $tripCriteria,
            'review' => null,
        ]);

        // Accommodations
        foreach ($payload['accommodations'] ?? [] as $abId => $accData) {
            $criteria = [
                'service_quality' => $accData['service_quality'] ?? null,
                'cleanliness' => $accData['cleanliness'] ?? null,
                'food' => $accData['food'] ?? null,
                'staff' => $accData['staff'] ?? null,
                'overall_tour_experience' => $accData['overall_tour_experience'] ?? null,
            ];

            ReviewItem::create([
                'review_id' => $review->id,
                'service_type' => 'accommodation',
                'service_id' => $accData['id'] ?? $abId,
                'criteria' => $criteria,
                'review' => $accData['review'] ?? null,
            ]);
        }

        // Activities
        foreach ($payload['activities'] ?? [] as $actId => $actData) {
            $criteria = [
                'equipment' => $actData['equipment'] ?? null,
                'tour_guide' => $actData['tour_guide'] ?? null,
                'safety' => $actData['safety'] ?? null,
                'staff' => $actData['staff'] ?? null,
                'overall_tour_experience' => $actData['overall_tour_experience'] ?? null,
            ];

            ReviewItem::create([
                'review_id' => $review->id,
                'service_type' => 'activity',
                'service_id' => $actData['id'] ?? $actId,
                'criteria' => $criteria,
                'review' => $actData['review'] ?? null,
            ]);
        }

        // Notify admin with the received payload
        $adminEmail = config('mail.from.address');
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new FeedbackReceived($trip, $payload));
        }

        return view('frontend.feedback-thankyou', compact('trip'));
    }
}
