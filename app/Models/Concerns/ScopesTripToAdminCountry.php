<?php

namespace App\Models\Concerns;

use App\Support\CountryAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

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
                $countryBookingScope = function (Builder $bookings) use ($countryId): void {
                    $bookings->where('country_id', $countryId);
                };
                $visibleTrip->whereHas('bookingRefs', $countryBookingScope)
                    ->orWhereHas('bookings', $countryBookingScope);

                foreach ([
                    'accommodationBookings' => 'accommodation_bookings',
                    'activityBookings' => 'activity_bookings',
                    'transportBookings' => 'transport_bookings',
                ] as $relation => $table) {
                    if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'trip_id')) {
                        continue;
                    }

                    $visibleTrip->orWhereHas($relation, $countryBookingScope);
                }
            });
        });
    }
}
