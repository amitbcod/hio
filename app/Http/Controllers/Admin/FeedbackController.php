<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewItem;
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

    public function updateItemStatus(Request $request, ReviewItem $item)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $item->update(['status' => $validated['status']]);

        return back()->with('success', 'Review item status updated successfully.');
    }
}
