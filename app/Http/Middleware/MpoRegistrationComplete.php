<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use App\Services\MpoRegistrationCompletionService;
use Closure;
use Illuminate\Http\Request;

class MpoRegistrationComplete
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->attributes->has('mpo_staff_user')) {
            return $next($request);
        }

        $mpo = $request->attributes->get('mpo_admin');
        abort_unless($mpo instanceof AdminUser, 403);

        $completion = app(MpoRegistrationCompletionService::class);
        if ($completion->state($mpo)['complete']) {
            return $next($request);
        }

        return redirect()->route($completion->nextRoute($mpo))
            ->with('warning', 'Complete your Profile, verified Compliance, active Collaboration Agreement, and Accounting Commission before accessing country administration.');
    }
}
