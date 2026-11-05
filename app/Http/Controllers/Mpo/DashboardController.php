<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Activity;
use App\Models\AdminUser;
use App\Models\Business;
use App\Models\Mpo;
use App\Models\Review;
use App\Models\Transport;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $mpo = $request->attributes->get('mpo_admin');
        abort_unless($mpo instanceof AdminUser, 403);

        $businesses = Business::query()
            ->where('status', 'pending')
            ->latest()
            ->get();
        $accommodations = Accommodation::with(['operator', 'business'])
            ->where(function ($query): void {
                $query->where('approval_status', 'Pending')
                    ->orWhere(function ($fallback): void {
                        $fallback->whereNull('approval_status')
                            ->where('status', Accommodation::STATUS_PENDING_APPROVAL);
                    });
            })
            ->orderByRaw('COALESCE(submitted_for_approval_at, created_at) DESC')
            ->get();
        $activities = Activity::with('operator')
            ->where(function ($query): void {
                $query->where('approval_status', 'Pending')
                    ->orWhere(function ($fallback): void {
                        $fallback->whereNull('approval_status')
                            ->where('status', Activity::STATUS_IN_REVIEW);
                    });
            })
            ->orderByRaw('COALESCE(submitted_for_approval_at, created_at) DESC')
            ->get();
        $transports = Transport::with(['operator.profile', 'vehicleName'])
            ->whereNotNull('submitted_for_approval_at')
            ->where(function ($query): void {
                $query->where('approval_status', 'Pending')
                    ->orWhere('status', Transport::STATUS_IN_REVIEW);
            })
            ->orderByRaw('COALESCE(submitted_for_approval_at, created_at) DESC')
            ->get();
        $pendingMpos = Mpo::query()
            ->where('country_of_operation', $mpo->country->name)
            ->where(function ($query): void {
                $query->where('admin_approve_flag', 0)
                    ->whereIn('account_status', ['pending_verification', 'active']);
            })
            ->latest()
            ->get();
        $feedbacks = Review::with(['trip.traveler'])
            ->whereHas('trip')
            ->latest()
            ->get();

        return view('mpo.dashboard.index', compact(
            'mpo',
            'businesses',
            'accommodations',
            'activities',
            'transports',
            'pendingMpos',
            'feedbacks',
        ));
    }

    public function approveMpo(Request $request, int $mpoId)
    {
        $actor = $request->attributes->get('mpo_admin');
        abort_unless($actor instanceof AdminUser, 403);

        $mpo = Mpo::query()
            ->whereKey($mpoId)
            ->where('country_of_operation', $actor->country->name)
            ->where('admin_approve_flag', 0)
            ->firstOrFail();
        $mpo->account_status = 'active';
        $mpo->admin_approve_flag = 1;
        $mpo->approved_by = $actor->id;
        $mpo->approved_at = now();
        $mpo->save();

        return redirect()->route('mpo.dashboard')->with('success', 'MPO account approved.');
    }

    public function rejectMpo(Request $request, int $mpoId)
    {
        $actor = $request->attributes->get('mpo_admin');
        abort_unless($actor instanceof AdminUser, 403);

        $mpo = Mpo::query()
            ->whereKey($mpoId)
            ->where('country_of_operation', $actor->country->name)
            ->where('admin_approve_flag', 0)
            ->firstOrFail();
        $mpo->account_status = 'suspended';
        $mpo->admin_approve_flag = 0;
        $mpo->save();

        return redirect()->route('mpo.dashboard')->with('success', 'MPO account rejected.');
    }
}
