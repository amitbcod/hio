<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Activity;
use App\Models\Operator;
use App\Models\ReviewItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    protected function getOperatorIds()
    {
        if (Auth::guard('operator')->check()) {
            return [Auth::guard('operator')->id()];
        }

        if (Auth::guard('operator_staff')->check()) {
            $businessId = Auth::guard('operator_staff')->user()->business_id;
            if (!$businessId) {
                abort(403, 'Unauthorized access.');
            }

            return Operator::where('business_id', $businessId)->pluck('id')->toArray();
        }

        abort(403, 'Unauthorized access.');
    }

    public function index()
    {
        if (!Auth::guard('operator')->check() && !Auth::guard('operator_staff')->check()) {
            return redirect()->route('operator.login');
        }

        $operatorIds = $this->getOperatorIds();

        $accommodationReviewItems = ReviewItem::where('service_type', 'accommodation')
            ->whereHas('parentReview.trip.accommodationBookings.accommodation', function ($query) use ($operatorIds) {
                $query->whereIn('operator_id', $operatorIds);
            })
            ->with(['parentReview.trip.traveler', 'parentReview.trip.accommodationBookings.accommodation'])
            ->orderByDesc('created_at')
            ->get();

        $activityReviewItems = ReviewItem::where('service_type', 'activity')
            ->whereHas('parentReview.trip.activityBookings.activity', function ($query) use ($operatorIds) {
                $query->whereIn('operator_id', $operatorIds);
            })
            ->with(['parentReview.trip.traveler', 'parentReview.trip.activityBookings.activity'])
            ->orderByDesc('created_at')
            ->get();

        $reviews = collect();

        foreach ($accommodationReviewItems as $item) {
            $booking = optional($item->parentReview->trip->accommodationBookings)->firstWhere('id', $item->service_id);
            $accommodation = optional($booking)->accommodation;

            if (!$accommodation || !$item->parentReview->trip) {
                continue;
            }

            $reviews->push([
                'trip_id' => $item->parentReview->trip->id,
                'traveler_name' => optional($item->parentReview->trip->traveler)->name ?? 'Unknown',
                'service_id' => $accommodation->id,
                'service_type' => 'accommodation',
                'service_name' => $accommodation->property_name,
                'rating' => $item->rating ?? 'N/A',
                'review_date' => $item->created_at,
            ]);
        }

        foreach ($activityReviewItems as $item) {
            $booking = optional($item->parentReview->trip->activityBookings)->firstWhere('id', $item->service_id);
            $activity = optional($booking)->activity;

            if (!$activity || !$item->parentReview->trip) {
                continue;
            }

            $reviews->push([
                'trip_id' => $item->parentReview->trip->id,
                'traveler_name' => optional($item->parentReview->trip->traveler)->name ?? 'Unknown',
                'service_id' => $activity->id,
                'service_type' => 'activity',
                'service_name' => $activity->activity_name,
                'rating' => $item->rating ?? 'N/A',
                'review_date' => $item->created_at,
            ]);
        }

        $reviews = $reviews->sortByDesc('review_date')->values();

        return view('operator.feedback.index', compact('reviews'));
    }

    public function show($serviceType, $serviceId)
    {
        if (!Auth::guard('operator')->check() && !Auth::guard('operator_staff')->check()) {
            return redirect()->route('operator.login');
        }

        $operatorIds = $this->getOperatorIds();

        if ($serviceType === 'accommodation') {
            $service = Accommodation::whereIn('operator_id', $operatorIds)->findOrFail($serviceId);
            $reviewItems = ReviewItem::where('service_type', 'accommodation')
                ->whereHas('parentReview.trip.accommodationBookings', function ($query) use ($serviceId) {
                    $query->where('accommodation_id', $serviceId);
                })
                ->with(['parentReview.trip.traveler', 'parentReview.trip.accommodationBookings.accommodation'])
                ->orderByDesc('created_at')
                ->get();
        } elseif ($serviceType === 'activity') {
            $service = Activity::whereIn('operator_id', $operatorIds)->findOrFail($serviceId);
            $reviewItems = ReviewItem::where('service_type', 'activity')
                ->whereHas('parentReview.trip.activityBookings', function ($query) use ($serviceId) {
                    $query->where('activity_id', $serviceId);
                })
                ->with(['parentReview.trip.traveler', 'parentReview.trip.activityBookings.activity'])
                ->orderByDesc('created_at')
                ->get();
        } else {
            abort(404);
        }

        return view('operator.feedback.show', compact('service', 'serviceType', 'reviewItems'));
    }
}
