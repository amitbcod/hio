<?php

namespace App\Http\Middleware;

use App\Models\AgentAccount;
use App\Models\AgentStaffUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentAccountNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('agent') ?? $request->user('agent_staff');
        $agent = $user instanceof AgentStaffUser ? $user->agent : $user;

        abort_unless(
            $agent instanceof AgentAccount && $agent->account_status !== 'suspended',
            Response::HTTP_FORBIDDEN
        );

        return $next($request);
    }
}
