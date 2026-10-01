<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminGroupTransportBookingReferenceBackfillTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'admin_group_reference_test',
            'database.connections.admin_group_reference_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('admin_group_reference_test');
        DB::setDefaultConnection('admin_group_reference_test');

        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->string('booking_type')->nullable();
            $table->unsignedBigInteger('booking_ref_id')->nullable();
        });

        Schema::create('transport_bookings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->unsignedBigInteger('booking_ref_id')->nullable();
        });
    }

    public function test_it_backfills_only_transport_rows_for_unambiguous_admin_group_orders(): void
    {
        DB::table('bookings')->insert([
            ['trip_id' => 203, 'booking_type' => 'open-group', 'booking_ref_id' => 21],
            ['trip_id' => 204, 'booking_type' => 'open-group', 'booking_ref_id' => 22],
            ['trip_id' => 204, 'booking_type' => 'open-group', 'booking_ref_id' => 23],
            ['trip_id' => 205, 'booking_type' => 'individual', 'booking_ref_id' => 24],
        ]);

        DB::table('transport_bookings')->insert([
            ['trip_id' => 203, 'booking_ref_id' => null],
            ['trip_id' => 203, 'booking_ref_id' => null],
            ['trip_id' => 203, 'booking_ref_id' => 99],
            ['trip_id' => 204, 'booking_ref_id' => null],
            ['trip_id' => 205, 'booking_ref_id' => null],
            ['trip_id' => null, 'booking_ref_id' => null],
        ]);

        $migration = require database_path('migrations/2026_10_01_000001_backfill_admin_group_transport_booking_refs.php');
        $migration->up();

        $this->assertSame([21, 21, 99], DB::table('transport_bookings')
            ->where('trip_id', 203)
            ->orderBy('id')
            ->pluck('booking_ref_id')
            ->all());
        $this->assertNull(DB::table('transport_bookings')->where('trip_id', 204)->value('booking_ref_id'));
        $this->assertNull(DB::table('transport_bookings')->where('trip_id', 205)->value('booking_ref_id'));
        $this->assertNull(DB::table('transport_bookings')->whereNull('trip_id')->value('booking_ref_id'));
    }
}