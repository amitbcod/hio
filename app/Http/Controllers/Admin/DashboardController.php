<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;

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

        // activate related operators
        \App\Models\Operator::where('business_id', $business->id)->update(['account_status' => 'active']);

        return redirect()->route('admin.dashboard')->with('success', 'Business approved and operators activated.');
    }

    public function rejectBusiness(Request $request, Business $business)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $business->status = 'suspended';
        $business->save();
        return redirect()->route('admin.dashboard')->with('success', 'Business rejected/suspended.');
    }
}