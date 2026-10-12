<?php

namespace App\Http\Middleware;

use App\Services\OperatorServiceAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictOperatorServiceModules
{
    public function __construct(private readonly OperatorServiceAccess $serviceAccess)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('operator') ?? $request->user('operator_staff');
        $selectedServices = $this->serviceAccess->selectedServices($user);
        $request->attributes->set('operator.selected_services', $selectedServices);

        $route = $request->route();
        $service = $this->serviceAccess->serviceForRouteName(
            $route?->getName(),
            $route?->parameters() ?? []
        );

        if ($service && ! in_array($service, $selectedServices, true)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
