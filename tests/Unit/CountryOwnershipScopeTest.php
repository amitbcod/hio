<?php

namespace Tests\Unit;

use App\Models\AccommodationRoom;
use App\Models\Review;
use App\Models\TransportServiceRoutePair;
use App\Support\CountryAccess;
use App\Support\CountryOwnership;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class CountryOwnershipScopeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.country_scope_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'database.default' => 'country_scope_test',
        ]);
        DB::purge('country_scope_test');
        CountryAccess::clearCache();
        $this->app->instance('request', Request::create('/admin/test'));

        Schema::create('admin_users', function (Blueprint $table): void {
            $table->id();
            $table->string('admin_type');
            $table->unsignedBigInteger('country_id')->nullable();
        });
        Schema::create('accommodations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('country_id')->nullable();
        });
        Schema::create('accommodation_rooms', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('accommodation_id');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->timestamps();
        });
        Schema::create('transport_service_route_pairs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('country_id');
            $table->string('service_type');
            $table->string('route_from');
            $table->string('route_to');
            $table->char('route_identity', 64)->nullable();
            $table->timestamps();
        });
        Schema::create('trips', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('traveler_account_id')->nullable();
            $table->timestamps();
        });
        foreach (['accommodation_bookings', 'activity_bookings', 'transport_bookings'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('trip_id')->nullable();
                $table->unsignedBigInteger('country_id')->nullable();
            });
        }
        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->unsignedBigInteger('traveler_account_id')->nullable();
            $table->integer('overall_rating')->nullable();
            $table->text('overall_review')->nullable();
            $table->timestamps();
        });

        DB::table('admin_users')->insert([
            ['id' => 1, 'admin_type' => 'mpo', 'country_id' => 10],
            ['id' => 2, 'admin_type' => 'global', 'country_id' => null],
        ]);
        DB::table('accommodations')->insert([
            ['id' => 1, 'country_id' => 10],
            ['id' => 2, 'country_id' => 20],
        ]);
        DB::table('accommodation_rooms')->insert([
            ['id' => 1, 'accommodation_id' => 1, 'country_id' => 10],
            ['id' => 2, 'accommodation_id' => 2, 'country_id' => 20],
        ]);
    }

    public function test_mpo_reads_only_its_country_and_created_child_records_inherit_parent_country(): void
    {
        session()->put('admin_id', 1);
        CountryAccess::clearCache();

        $this->assertSame([1], AccommodationRoom::query()->orderBy('id')->pluck('id')->all());

        $room = AccommodationRoom::create(['accommodation_id' => 1]);
        $this->assertSame(10, (int) $room->country_id);
    }

    public function test_mpo_cannot_create_child_data_for_another_country_parent(): void
    {
        session()->put('admin_id', 1);
        CountryAccess::clearCache();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        AccommodationRoom::create(['accommodation_id' => 2]);
    }

    public function test_global_admin_can_read_records_from_all_countries(): void
    {
        session()->put('admin_id', 2);
        CountryAccess::clearCache();

        $this->assertSame([1, 2], AccommodationRoom::query()->orderBy('id')->pluck('id')->all());
    }

    public function test_itinerary_cannot_mix_a_country_with_another_countrys_service_or_room(): void
    {
        CountryOwnership::assertItineraryCountry([
            ['accommodation' => 1, 'rooms' => [1]],
        ], 10);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        CountryOwnership::assertItineraryCountry([
            ['accommodation' => 2, 'rooms' => [2]],
        ], 10);
    }

    public function test_itinerary_country_validation_ignores_non_day_metadata(): void
    {
        CountryOwnership::assertItineraryCountry([
            0 => ['accommodation' => 1, 'rooms' => [1]],
            'pricing_modes' => ['transport' => 'package_rate'],
            'discounts' => ['activity' => '10'],
        ], 10);

        $this->assertTrue(true);
    }

    public function test_transport_route_identity_is_stable_and_includes_each_route_field(): void
    {
        $identity = TransportServiceRoutePair::identityFor('airport_transfer', 'Airport', 'North');

        $this->assertSame(
            $identity,
            TransportServiceRoutePair::identityFor('airport_transfer', 'Airport', 'North')
        );
        $this->assertNotSame($identity, TransportServiceRoutePair::identityFor('hotel_transfer', 'Airport', 'North'));
        $this->assertNotSame($identity, TransportServiceRoutePair::identityFor('airport_transfer', 'North', 'Airport'));
        $this->assertSame(64, strlen($identity));
    }

    public function test_transport_route_model_calculates_the_indexable_identity_when_saved(): void
    {
        $routePair = TransportServiceRoutePair::create([
            'country_id' => 10,
            'service_type' => 'airport_transfer',
            'route_from' => 'Airport',
            'route_to' => 'North',
        ]);

        $this->assertSame(
            TransportServiceRoutePair::identityFor('airport_transfer', 'Airport', 'North'),
            $routePair->route_identity
        );
    }

    public function test_mpo_feedback_query_excludes_reviews_whose_trips_are_out_of_country_scope(): void
    {
        session()->put('admin_id', 1);
        CountryAccess::clearCache();

        DB::table('trips')->insert([
            ['id' => 1, 'traveler_account_id' => null],
            ['id' => 2, 'traveler_account_id' => null],
        ]);
        DB::table('accommodation_bookings')->insert([
            ['trip_id' => 1, 'country_id' => 10],
            ['trip_id' => 2, 'country_id' => 20],
        ]);
        DB::table('reviews')->insert([
            ['id' => 1, 'trip_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'trip_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'trip_id' => 999, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $visibleFeedbackIds = Review::with(['trip.traveler'])
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $this->assertSame([1], $visibleFeedbackIds);
    }

    public function test_dashboard_renders_feedback_with_a_missing_trip_as_not_available(): void
    {
        $review = new Review([
            'id' => 1,
            'trip_id' => 999,
            'overall_rating' => 5,
        ]);
        $review->setRelation('trip', null);

        $html = view('admin.dashboard.index', [
            'businesses' => collect(),
            'pendingAccommodations' => collect(),
            'pendingActivities' => collect(),
            'pendingTransports' => collect(),
            'pendingMpos' => collect(),
            'feedbacks' => collect([$review]),
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('>N/A</td>', $html);
        $this->assertStringNotContainsString('Attempt to read property', $html);
    }
}
