<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Operator;
use App\Models\OperatorProfile;
use App\Models\OperatorRegistrationProgress;

class ProfileController extends Controller
{
    public function showProfile()
    {
        $operator = auth()->user();
        $profile = OperatorProfile::where('operator_id', $operator->operator_id)->first();
        // Prefer business-scoped progress when available
        $progress = !empty($operator->business_id)
            ? OperatorRegistrationProgress::where('business_id', $operator->business_id)->first()
            : OperatorRegistrationProgress::where('operator_id', $operator->operator_id)->first();
        return view('operator.profile.index', compact('operator', 'profile', 'progress'));
    }
    // Add methods for each profile step as needed
}
