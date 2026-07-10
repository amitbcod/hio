<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Accommodation;
use App\Models\Activity;
use App\Models\Transport;
use App\Models\Review;
use Illuminate\Support\Facades\Mail;
use App\Mail\BusinessApproved;

class DashboardController extends Controller
{
    public function __construct()
    {
        // simple session based guard for admin
        if (!session('admin_id')) {
            // nothing to do here; actions will redirect as needed
        }
    }

    public function index()
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $businesses = Business::where('status', 'pending')->orderBy('created_at', 'desc')->get();

        $pendingAccommodations = Accommodation::with(['operator', 'business'])
            ->where(function ($query) {
                $query->where('approval_status', 'Pending')
                    ->orWhere(function ($fallbackQuery) {
                        $fallbackQuery->whereNull('approval_status')
                            ->where('status', Accommodation::STATUS_PENDING_APPROVAL);
                    });
            })
            ->orderByRaw('COALESCE(submitted_for_approval_at, created_at) DESC')
            ->get();

        $pendingActivities = Activity::with(['operator'])
            ->where(function ($query) {
                $query->where('approval_status', 'Pending')
                    ->orWhere(function ($fallbackQuery) {
                        $fallbackQuery->whereNull('approval_status')
                            ->where('status', Activity::STATUS_IN_REVIEW);
                    });
            })
            ->orderByRaw('COALESCE(submitted_for_approval_at, created_at) DESC')
            ->get();

        $pendingTransports = Transport::with(['operator'])
            ->whereNotNull('submitted_for_approval_at')
            ->where(function ($query) {
                $query->where('approval_status', 'Pending')
                    ->orWhere('status', Transport::STATUS_IN_REVIEW);
            })
            ->orderByRaw('COALESCE(submitted_for_approval_at, created_at) DESC')
            ->get();

        $feedbacks = Review::with(['trip.traveler'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.dashboard.index', compact('businesses', 'pendingAccommodations', 'pendingActivities', 'pendingTransports', 'feedbacks'));
    }

    public function approveBusiness(Request $request, Business $business)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $business->status = 'active';
        $business->save();

        // send notification to business owner and authorised operators
        $owner = \App\Models\Operator::where('business_id', $business->id)->where('is_owner', 'yes')->first();
        if ($owner) {
            Mail::to($owner->email)->send(new BusinessApproved($business, $owner));
        }

        // authorised operators: non-owners with active account_status
        $authorised = \App\Models\Operator::where('business_id', $business->id)
                        ->where('is_owner', 'no')
                        ->where('account_status', 'active')
                        ->get();

        foreach ($authorised as $op) {
            Mail::to($op->email)->send(new BusinessApproved($business, $op));
        }

        return redirect()->route('admin.dashboard')->with('success', 'Business has been approved.');
    }

    public function rejectBusiness(Request $request, Business $business)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $business->status = 'suspended';
        $business->save();
        return redirect()->route('admin.dashboard')->with('success', 'Business rejected/suspended.');
    }

    public function approveAccommodation(Request $request, Accommodation $accommodation)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $data = $request->validate([
            'approval_notes' => 'nullable|string|max:2000',
        ]);

        $adminId = (int) session('admin_id');

        $accommodation->update([
            'approval_status' => 'Approved',
            'status' => Accommodation::STATUS_ACTIVE,
            'approved_at' => now(),
            'approved_by' => $adminId > 0 ? $adminId : null,
            'approval_notes' => $data['approval_notes'] ?? null,
            'is_published' => true,
            'published_at' => now(),
            'is_visible_to_travellers' => true,
            'step13_publish' => 1,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Accommodation approved and now visible on frontend.');
    }

    public function rejectAccommodation(Request $request, Accommodation $accommodation)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $data = $request->validate([
            'approval_notes' => 'nullable|string|max:2000',
        ]);

        $adminId = (int) session('admin_id');

        $accommodation->update([
            'approval_status' => 'Rejected',
            'status' => Accommodation::STATUS_IN_SETUP,
            'approved_at' => null,
            'approved_by' => $adminId > 0 ? $adminId : null,
            'approval_notes' => $data['approval_notes'] ?? null,
            'is_published' => false,
            'published_at' => null,
            'is_visible_to_travellers' => false,
            'step13_publish' => 1,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Accommodation rejected. Operator can update and resubmit.');
    }

    public function approveActivity(Request $request, Activity $activity)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $data = $request->validate([
            'approval_notes' => 'nullable|string|max:2000',
        ]);

        $adminId = (int) session('admin_id');

        $activity->update([
            'approval_status' => 'Approved',
            'status' => Activity::STATUS_ACTIVE,
            'approved_at' => now(),
            'approved_by' => $adminId > 0 ? $adminId : null,
            'approval_notes' => $data['approval_notes'] ?? null,
            'step13_publish' => 1,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Activity approved and now visible on frontend.');
    }

    public function rejectActivity(Request $request, Activity $activity)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $data = $request->validate([
            'approval_notes' => 'nullable|string|max:2000',
        ]);

        $adminId = (int) session('admin_id');

        $activity->update([
            'approval_status' => 'Rejected',
            'status' => Activity::STATUS_IN_REVIEW,
            'approved_at' => null,
            'approved_by' => $adminId > 0 ? $adminId : null,
            'approval_notes' => $data['approval_notes'] ?? null,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Activity rejected. Operator can update and resubmit.');
    }

    public function approveTransport(Request $request, Transport $transport)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $data = $request->validate([
            'approval_notes' => 'nullable|string|max:2000',
        ]);

        $adminId = (int) session('admin_id');

        $transport->update([
            'approval_status' => 'Approved',
            'status' => Transport::STATUS_ACTIVE,
            'approved_at' => now(),
            'approved_by' => $adminId > 0 ? $adminId : null,
            'approval_notes' => $data['approval_notes'] ?? null,
            'is_published' => true,
            'published_at' => now(),
            'is_visible_to_travellers' => true,
            'step7_publish' => 1,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Transport approved and now visible on frontend.');
    }

    public function rejectTransport(Request $request, Transport $transport)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $data = $request->validate([
            'approval_notes' => 'nullable|string|max:2000',
        ]);

        $adminId = (int) session('admin_id');

        $transport->update([
            'approval_status' => 'Rejected',
            'status' => Transport::STATUS_DRAFT,
            'approved_at' => null,
            'approved_by' => $adminId > 0 ? $adminId : null,
            'approval_notes' => $data['approval_notes'] ?? null,
            'is_published' => false,
            'published_at' => null,
            'is_visible_to_travellers' => false,
            'step7_publish' => 0,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Transport rejected. Operator can update and resubmit.');
    }
}