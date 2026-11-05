<?php

namespace App\Support;

use Illuminate\Http\Request;

class MpoAdminRouteAccess
{
    private const READ_ROUTES = [
        'admin.trips.index',
        'admin.trips.show',
        'admin.operators.index',
        'admin.agents.index',
        'admin.agents.show',
        'admin.travellers.index',
        'admin.feedback.index',
        'admin.feedback.show',
        'admin.accommodation.index',
        'admin.accommodation.bookings',
        'admin.activity.index',
        'admin.activity.bookings',
        'admin.transport.bookings',
        'admin.payment-transactions.index',
        'admin.payment-transactions.show',
        'admin.packages.index',
        'admin.groups.index',
        'admin.vehicle-types.index',
        'admin.vehicle-names.index',
        'admin.regions.index',
        'admin.transport-service-route-pairs.index',
        'admin.places.index',
    ];

    public static function allows(Request $request): bool
    {
        return $request->isMethod('GET')
            && in_array($request->route()?->getName(), self::READ_ROUTES, true);
    }

    public static function showsInSidebar(string $routeName): bool
    {
        return in_array($routeName, self::READ_ROUTES, true);
    }
}
