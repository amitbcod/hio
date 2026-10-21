<?php

namespace Tests\Unit;

use App\Models\AccommodationBooking;
use App\Models\TravelerAccount;
use App\Models\Trip;
use App\Services\TripListingFilters;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use PHPUnit\Framework\TestCase;

class TripListingFiltersTest extends TestCase
{
    public function test_travel_party_size_uses_adults_plus_children_and_treats_null_as_zero(): void
    {
        $cases = [
            [1, 0, 1],
            [2, 1, 3],
            [2, 3, 5],
            [1, null, 1],
            [null, 2, 2],
        ];
        $filters = new TripListingFilters();

        foreach ($cases as [$adults, $children, $expected]) {
            $trip = new Trip();
            $trip->setRelation('traveler', new TravelerAccount());
            $trip->setRelation('travellers', new EloquentCollection());
            $trip->setRelation('accommodationBookings', new EloquentCollection([
                new AccommodationBooking(['adults' => $adults, 'children' => $children]),
            ]));
            $trip->setRelation('activityBookings', new EloquentCollection());
            $trip->setRelation('transportBookings', new EloquentCollection());

            $this->assertSame($expected, $filters->resolveTravellerDisplay($trip)['party_size']);
        }
    }
}
