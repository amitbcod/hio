<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $feedbacks = Review::with(['trip.traveler'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.feedback.index', compact('feedbacks'));
    }

    public function show(Review $review)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $review->load(['trip.traveler', 'trip.accommodationBookings.accommodation', 'trip.activityBookings.activity']);

        return view('admin.feedback.show', compact('review'));
    }
}
