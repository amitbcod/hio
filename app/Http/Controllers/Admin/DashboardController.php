<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
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
        return view('admin.dashboard.index', compact('businesses'));
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
}