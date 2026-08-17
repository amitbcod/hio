<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ControllerVerification;
use App\Models\Mpo;
use App\Models\Business;

class ControllerVerificationController extends Controller
{
    public function show($token)
    {
        $cv = ControllerVerification::where('token', $token)->firstOrFail();
        if ($cv->expires_at && now()->gt($cv->expires_at)) {
            $cv->status = 'expired';
            $cv->save();
            return view('mpo.registration.controller_verify', compact('cv'))->with('error', 'This verification token has expired.');
        }

        $owner = Mpo::where('email', $cv->owner_email)->first();
        $requester = $cv->requester;
        return view('mpo.registration.controller_verify', compact('cv', 'owner', 'requester'));
    }

    public function accept(Request $request, $token)
    {
        $cv = ControllerVerification::where('token', $token)->firstOrFail();
        if ($cv->status !== 'pending') {
            return redirect()->route('mpo.register.step2')->with('error', 'Verification not in pending state.');
        }

        $user = auth()->user();
        if (!$user) {
            session(['accept_token' => $token]);
            return redirect()->to(route('mpo.login').'?accept_token='.urlencode($token))->with('info', 'Please login with the owner account to accept this request.')->with('accept_token', $token);
        }

        if ($user->email !== $cv->owner_email) {
            return redirect()->route('mpo.login')->with('error', 'Please login with the owner account to accept this request.');
        }

        $cv->acceptBy($user);

        if (empty($user->business_id)) {
            $user->business_id = $cv->business_id;
            $user->save();
        }

        try {
            if ($cv->requester) {
                $rq = $cv->requester;
                $rq->account_status = 'active';
                $rq->admin_approve_flag = 0;
                $rq->save();
            }
        } catch (\Exception $e) {
            \Log::error('Mpo\ControllerVerificationController::accept - activating requester failed', ['err' => $e->getMessage()]);
        }

        return redirect()->route('mpo.register.step2')->with('success', 'You have accepted the request. Business is pending admin approval.');
    }

    public function claim(Request $request, $token)
    {
        $cv = ControllerVerification::where('token', $token)->firstOrFail();

        if ($cv->expires_at && now()->gt($cv->expires_at)) {
            $cv->status = 'expired';
            $cv->save();
            return redirect()->route('mpo.register.step2')->with('error', 'This verification token has expired.');
        }

        if ($cv->status !== 'pending') {
            return redirect()->route('mpo.register.step2')->with('error', 'Verification not in pending state.');
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'owner_email' => 'required|email',
            'password' => ['required', 'confirmed', 'min:8'],
            'agreement_type' => 'required|in:Listing Only,OTO,Widget Only,OTO + Widget,Full Service',
        ]);

        if (($request->owner_email ?? '') !== $cv->owner_email) {
            return back()->withErrors(['owner_email' => 'Owner email mismatch.']);
        }

        if (!$request->has('confirm_authority')) {
            return back()->withErrors(['confirm_authority' => 'You must confirm authority to claim this business.']);
        }

        $owner = Mpo::create([
            'mpo_id' => uniqid('MP'),
            'user_type' => 'MPO',
            'is_owner' => 'yes',
            'email' => $cv->owner_email,
            'phone' => $request->phone ?? null,
            'full_name' => $request->full_name,
            'business_legal_name' => $cv->business->legal_name ?? null,
            'country_of_operation' => $cv->business->country ?? null,
            'agreement_type' => $request->agreement_type,
            'booking_registration_type' => $request->agreement_type,
            'account_status' => 'pending_verification',
            'admin_approve_flag' => 0,
            'password_hash' => bcrypt($request->password),
            'business_id' => $cv->business_id,
        ]);

        try {
            if (!empty($request->agreement_type) && $cv->business) {
                $cv->business->agreement_type = $request->agreement_type;
                $cv->business->save();
            }
        } catch (\Exception $e) {
            \Log::error('Mpo\ControllerVerificationController::claim - saving business agreement_type failed', ['err' => $e->getMessage()]);
        }

        $cv->acceptBy($owner);

        try {
            if ($cv->requester) {
                $rq = $cv->requester;
                $rq->account_status = 'active';
                $rq->admin_approve_flag = 0;
                $rq->save();
            }
        } catch (\Exception $e) {
            \Log::error('Mpo\ControllerVerificationController::claim - activating requester failed', ['err' => $e->getMessage()]);
        }

        return redirect()->route('mpo.login')->with('success', 'Owner account created. Please login to continue and confirm the business.');
    }

    public function reject(Request $request, $token)
    {
        $cv = ControllerVerification::where('token', $token)->firstOrFail();
        $cv->status = 'rejected';
        $cv->notes = $request->notes ?? null;
        $cv->save();
        return redirect()->route('mpo.register.step2')->with('success', 'Verification request rejected.');
    }
}
