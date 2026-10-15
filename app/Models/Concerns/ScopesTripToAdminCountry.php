<?php

namespace App\Models\Concerns;

use App\Support\CountryAccess;
use Illuminate\Database\Eloquent\Builder;

trait ScopesTripToAdminCountry
{
    protected static function bootScopesTripToAdminCountry(): void
    {
        static::addGlobalScope('admin_country', function (Builder $query): void {
            if (! CountryAccess::isMpoAdmin()) {
                return;
            }

            $countryId = CountryAccess::countryId();
            if ($countryId === null) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->where(function (Builder $visibleTrip) use ($countryId): void {
                foreach (['accommodationBookings', 'activityBookings', 'transportBookings'] as $index => $relation) {
                    $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                    $visibleTrip->{$method}($relation, function (Builder $bookings) use ($countryId): void {
                        $bookings->where('country_id', $countryId);
                    });
                }
            });
        });
    }
}
