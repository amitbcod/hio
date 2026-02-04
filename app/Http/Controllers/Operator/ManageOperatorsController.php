<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Operator;
use Illuminate\Support\Facades\Mail;
use App\Mail\OperatorStatusChanged;

class ManageOperatorsController extends Controller
{
    public function index()
    {
        $operator = auth()->user();
        // Only owners can manage
        if (!$operator || ($operator->is_owner ?? 'no') !== 'yes') {
            return redirect()->route('operator.profile')->with('error', 'Unauthorized access.');
        }
        if (empty($operator->business_id)) {
            return redirect()->route('operator.profile')->with('error', 'No business linked to your account.');
        }
        $operators = Operator::where('business_id', $operator->business_id)
            ->where('is_owner', 'no')
            ->orderBy('full_name')
            ->get();
        return view('operator.manage.operators', compact('operators'));
    }

    public function updateStatus(Request $request, $id)
    {
        $operator = auth()->user();
        // permission checks
        if (!$operator || ($operator->is_owner ?? 'no') !== 'yes') {
            return redirect()->route('operator.profile')->with('error', 'Unauthorized action.');
        }
        $request->validate([
            'status' => 'required|in:pending_verification,active,suspended,archived',
        ]);

        $target = Operator::where('id', $id)
            ->where('business_id', $operator->business_id)
            ->where('is_owner', 'no')
            ->firstOrFail();

        $old = $target->account_status;
        $target->account_status = $request->status;
        $target->save();

        // send notification email
        try {
            Mail::to($target->email)->send(new OperatorStatusChanged($target, $old, $request->status));
        } catch (\Exception $e) {
            \Log::error('ManageOperatorsController::updateStatus - mail failed', ['err' => $e->getMessage()]);
        }

        return redirect()->route('operator.manage.operators.index')->with('success', 'Operator status updated.');
    }
}
