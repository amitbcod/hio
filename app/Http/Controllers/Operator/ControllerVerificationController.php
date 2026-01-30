<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ControllerVerification;
use App\Models\Operator;
use App\Models\Business;

class ControllerVerificationController extends Controller
{
    public function show($token)
    {
        $cv = ControllerVerification::where('token', $token)->firstOrFail();
        // check expiry
        if ($cv->expires_at && now()->gt($cv->expires_at)) {
            $cv->status = 'expired';
            $cv->save();
            return view('operator.registration.controller_verify', compact('cv'))->with('error', 'This verification token has expired.');
        }

        // check if owner exists
        $owner = Operator::where('email', $cv->owner_email)->first();

        return view('operator.registration.controller_verify', compact('cv', 'owner'));
    }

    public function accept(Request $request, $token)
    {
        $cv = ControllerVerification::where('token', $token)->firstOrFail();
        if ($cv->status !== 'pending') {
            return redirect()->route('operator.register.step2')->with('error', 'Verification not in pending state.');
        }

        // require login and match owner
        $user = auth()->user();
        if (!$user || $user->email !== $cv->owner_email) {
            return redirect()->route('operator.login')->with('error', 'Please login with the owner account to accept this request.');
        }

        // mark accepted
        $cv->acceptBy($user);

        // ensure owner has business_id
        if (empty($user->business_id)) {
            $user->business_id = $cv->business_id;
            $user->save();
        }

        // notify requester (not implemented - simple flash)
        return redirect()->route('operator.register.step2')->with('success', 'You have accepted the request. Business is pending admin approval.');
    }

    public function claim(Request $request, $token)
    {
        $cv = ControllerVerification::where('token', $token)->firstOrFail();

        // check expiry (mirror behaviour in show)
        if ($cv->expires_at && now()->gt($cv->expires_at)) {
            $cv->status = 'expired';
            $cv->save();
            return redirect()->route('operator.register.step2')->with('error', 'This verification token has expired.');
        }

        if ($cv->status !== 'pending') {
            return redirect()->route('operator.register.step2')->with('error', 'Verification not in pending state.');
        }

        \Log::info('ControllerVerificationController::claim - start', ['token' => $cv->token]);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'password' => ['required','confirmed','min:8'],
        ]);

        \Log::info('ControllerVerificationController::claim - validated', ['owner_email' => $request->owner_email]);

        // validation: owner_email must match verification
        if (($request->owner_email ?? '') !== $cv->owner_email) {
            \Log::info('ControllerVerificationController::claim - owner_email mismatch', ['provided' => $request->owner_email, 'expected' => $cv->owner_email]);
            return back()->withErrors(['owner_email' => 'Owner email mismatch.']);
        }

        // ensure confirm_authority accepted
        if (!$request->has('confirm_authority')) {
            \Log::info('ControllerVerificationController::claim - authority not confirmed');
            return back()->withErrors(['confirm_authority' => 'You must confirm authority to claim this business.']);
        }

        // create owner operator
        $owner = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => $cv->owner_email,
            'phone' => $request->phone ?? null,
            'full_name' => $request->full_name,
            'business_legal_name' => $cv->business->legal_name ?? null,
            'account_status' => 'pending_verification',
            'password_hash' => bcrypt($request->password),
            'business_id' => $cv->business_id,
        ]);

        \Log::info('ControllerVerificationController::claim - owner created', ['owner_id' => $owner->id ?? null, 'email' => $owner->email ?? null]);

        // mark accepted
        $cv->acceptBy($owner);
        \Log::info('ControllerVerificationController::claim - verification accepted', ['cv_status' => $cv->status]);

        // reload from DB to see persisted value
        $cv_db = ControllerVerification::find($cv->id);
        \Log::info('ControllerVerificationController::claim - verification from DB', ['db_status' => $cv_db->status, 'db_accepted_by' => $cv_db->accepted_by]);

        // notify requester (if exists)
        try {
            if ($cv->requester) {
                \Mail::to($cv->requester->email)->send(new \App\Mail\OwnerClaimedToRequester($cv));
            }
            // notify admins
            $adminEmails = \App\Models\AdminUser::where('status', 'active')->pluck('email');
            foreach ($adminEmails as $a) {
                \Mail::to($a)->send(new \App\Mail\AdminOwnerClaimedNotification($cv));
            }
        } catch (\Exception $e) {
            \Log::error('ControllerVerificationController::claim - mail error', ['err' => $e->getMessage()]);
            // swallow for now
        }

        return redirect()->route('operator.login')->with('success', 'Owner account created. Please login to continue and confirm the business.');
    }

    public function reject(Request $request, $token)
    {
        $cv = ControllerVerification::where('token', $token)->firstOrFail();
        $cv->status = 'rejected';
        $cv->notes = $request->notes ?? null;
        $cv->save();
        return redirect()->route('operator.register.step2')->with('success', 'Verification request rejected.');
    }
}
