<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $countryTables = [
        'businesses',
        'operators',
        'accommodations',
        'activities',
        'transports',
        'packages',
        'groups',
        'regions',
        'places',
        'accommodation_rooms',
        'accommodation_rates',
        'accommodation_media',
        'accommodation_inventory',
        'accommodation_compliance',
        'accommodation_promotions',
        'accommodation_fees',
        'activity_variants',
        'activity_rates',
        'activity_allotments',
        'activity_blackout_dates',
        'activity_promotions',
        'activity_addons',
        'activity_policies',
        'activity_scheduling_timeslots',
        'activity_operations_staffing',
        'activity_compliance',
        'activity_accounting',
        'activity_seo_social',
        'transport_rates',
        'transport_routes',
        'transport_vehicles',
        'transport_booking_assignments',
        'transport_booking_drivers',
        'transport_service_route_pairs',
        'operator_drivers',
        'operator_profiles',
        'operator_registration_progress',
        'operator_legal_compliance',
        'operator_collaboration_agreements',
        'operator_accounting_payouts',
        'operator_payouts',
        'operator_payout_history',
        'operator_role_access_mapping',
        'operator_service_operations',
        'operator_status_review',
        'operator_system_processes',
        'operator_users',
        'accommodation_bookings',
        'activity_bookings',
        'transport_bookings',
        'booking_line_items',
        'bookings',
        'booking_refs',
        'payment_transactions',
        'booking_guests',
        'booking_schedule_changes',
        'trip_action_deadlines',
        'trip_action_deadline_settings',
        'admin_users',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 191)->unique();
                $table->string('iso_code', 2)->nullable()->unique();
                $table->string('currency_code', 3)->nullable();
                $table->string('time_zone', 64)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->engine = 'InnoDB';
            });
        }
        $this->ensureCountryTableSupportsForeignKeys();

        $now = now();
        DB::table('countries')->insertOrIgnore([
            'name' => 'Mauritius',
            'iso_code' => 'MU',
            'currency_code' => 'MUR',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if (Schema::hasTable('admin_users') && ! Schema::hasColumn('admin_users', 'admin_type')) {
            Schema::table('admin_users', function (Blueprint $table): void {
                $table->string('admin_type', 20)->default('global');
            });
        }

        if (Schema::hasTable('trip_action_deadline_settings')
            && ! Schema::hasColumn('trip_action_deadline_settings', 'id')) {
            Schema::table('trip_action_deadline_settings', function (Blueprint $table): void {
                $table->dropPrimary();
                $table->bigIncrements('id');
            });
        }

        $countryIds = DB::table('countries')->pluck('id', 'name');
        $mauritiusId = (int) $countryIds->get('Mauritius');

        foreach ($this->countryTables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'country_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->unsignedBigInteger('country_id')->nullable()->index();
            });
        }

        if (Schema::hasTable('businesses')) {
            DB::table('businesses')->update(['country_id' => $mauritiusId]);
        }

        $this->copyFromParent('operators', 'business_id', 'businesses');
        $this->copyProductCountry('accommodations', $countryIds, $mauritiusId);
        $this->copyFromOperator('activities', $mauritiusId);
        $this->copyFromOperator('transports', $mauritiusId);
        $this->fillCountry('regions', $mauritiusId);
        $this->fillCountry('places', $mauritiusId);
        $this->copyFromParent('accommodation_bookings', 'accommodation_id', 'accommodations');
        $this->copyFromParent('activity_bookings', 'activity_id', 'activities');
        $this->copyFromParent('transport_bookings', 'transport_id', 'transports');
        foreach ([
            'accommodation_rooms',
            'accommodation_rates',
            'accommodation_media',
            'accommodation_inventory',
            'accommodation_compliance',
            'accommodation_promotions',
            'accommodation_fees',
        ] as $table) {
            $this->copyFromParent($table, 'accommodation_id', 'accommodations');
        }
        foreach ([
            'activity_variants',
            'activity_rates',
            'activity_allotments',
            'activity_blackout_dates',
            'activity_promotions',
            'activity_addons',
            'activity_policies',
            'activity_scheduling_timeslots',
            'activity_operations_staffing',
            'activity_compliance',
            'activity_accounting',
            'activity_seo_social',
        ] as $table) {
            $this->copyFromParent($table, 'activity_id', 'activities');
        }
        foreach (['transport_rates', 'transport_routes', 'transport_vehicles'] as $table) {
            $this->copyFromParent($table, 'transport_id', 'transports');
        }
        $this->copyFromParent('transport_booking_assignments', 'transport_booking_id', 'transport_bookings');
        $this->copyFromParent('transport_booking_drivers', 'transport_booking_id', 'transport_bookings');
        $this->fillCountry('transport_service_route_pairs', $mauritiusId);
        foreach ([
            'operator_profiles',
            'operator_registration_progress',
            'operator_legal_compliance',
            'operator_collaboration_agreements',
            'operator_accounting_payouts',
            'operator_payouts',
            'operator_payout_history',
            'operator_role_access_mapping',
            'operator_service_operations',
            'operator_status_review',
            'operator_system_processes',
            'operator_users',
        ] as $table) {
            if ($this->canCopy($table, 'business_id', 'businesses')) {
                $this->copyFromParent($table, 'business_id', 'businesses');
            }
            $this->copyFromOperatorString($table, $mauritiusId);
        }
        $this->copyFromParent('operator_drivers', 'business_id', 'businesses');
        $this->copyFromOperatorString('operator_drivers', $mauritiusId);
        $this->copyBookingItemCountries();
        $this->copyBookingCountries();
        $this->copyBookingReferenceCountries();
        $this->copyPaymentCountries();
        $this->copyGuestCountries();
        $this->copyScheduleChangeCountries();
        $this->copyDeadlineCountries();
        if (Schema::hasTable('trip_action_deadline_settings')) {
            DB::table('trip_action_deadline_settings')
                ->whereNull('country_id')
                ->update(['country_id' => $mauritiusId]);
            $this->addUniqueIndexIfMissing(
                'trip_action_deadline_settings',
                ['country_id', 'action_type'],
                'trip_deadline_settings_country_action_unique'
            );
        }
        if (Schema::hasTable('transport_service_route_pairs')) {
            if (! Schema::hasColumn('transport_service_route_pairs', 'route_identity')) {
                Schema::table('transport_service_route_pairs', function (Blueprint $table): void {
                    $table->char('route_identity', 64)->nullable();
                });
            }
            $this->backfillTransportRouteIdentity();
            $this->dropUniqueIndexesForColumns(
                'transport_service_route_pairs',
                ['service_type', 'route_from', 'route_to']
            );
            $this->dropUniqueIndexesForColumns('transport_service_route_pairs', ['route_identity']);
            $this->addUniqueIndexIfMissing(
                'transport_service_route_pairs',
                ['country_id', 'route_identity'],
                'tsrp_country_unique_pair'
            );
        }
        $this->fillCountry('packages', $mauritiusId);
        $this->fillCountry('groups', $mauritiusId);
        $this->scopeGeographicUniqueKeys();

        foreach ($this->countryTables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'country_id')) {
                continue;
            }

            $foreignKeyName = 'fk_' . $table . '_country';
            if (! $this->foreignKeyExists($table, $foreignKeyName)) {
                Schema::table($table, function (Blueprint $blueprint) use ($foreignKeyName): void {
                    $blueprint->foreign('country_id', $foreignKeyName)
                        ->references('id')
                        ->on('countries')
                        ->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('trip_action_deadline_settings')
            && Schema::hasColumn('trip_action_deadline_settings', 'country_id')) {
            $this->dropIndexIfPresent('trip_action_deadline_settings', 'trip_deadline_settings_country_action_unique');
        }
        if (Schema::hasTable('transport_service_route_pairs')
            && Schema::hasColumn('transport_service_route_pairs', 'country_id')) {
            $this->dropIndexIfPresent('transport_service_route_pairs', 'tsrp_country_unique_pair');
        }
        if (Schema::hasTable('regions') && Schema::hasColumn('regions', 'country_id')) {
            $this->dropIndexIfPresent('regions', 'regions_country_name_unique');
        }
        if (Schema::hasTable('places') && Schema::hasColumn('places', 'country_id')) {
            $this->dropIndexIfPresent('places', 'places_country_place_name_unique');
        }

        foreach (array_reverse($this->countryTables) as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'country_id')) {
                continue;
            }

            $foreignKeyName = 'fk_' . $table . '_country';
            if ($this->foreignKeyExists($table, $foreignKeyName)) {
                Schema::table($table, function (Blueprint $blueprint) use ($foreignKeyName): void {
                    $blueprint->dropForeign($foreignKeyName);
                });
            }
            $this->dropIndexIfPresent($table, $table . '_country_id_index');
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropColumn('country_id');
            });
        }

        if (Schema::hasTable('regions')) {
            Schema::table('regions', function (Blueprint $table): void {
                $table->unique('name', 'regions_name_unique');
            });
        }
        if (Schema::hasTable('places')) {
            Schema::table('places', function (Blueprint $table): void {
                $table->unique('place_name', 'places_place_name_unique');
            });
        }
        if (Schema::hasTable('trip_action_deadline_settings') && Schema::hasColumn('trip_action_deadline_settings', 'id')) {
            Schema::table('trip_action_deadline_settings', function (Blueprint $table): void {
                $table->dropPrimary();
                $table->dropColumn('id');
                $table->primary('action_type');
            });
        }
        if (Schema::hasTable('transport_service_route_pairs')) {
            $this->addUniqueIndexIfMissing(
                'transport_service_route_pairs',
                ['route_identity'],
                'tsrp_route_identity_unique'
            );
        }
        if (Schema::hasTable('admin_users') && Schema::hasColumn('admin_users', 'admin_type')) {
            Schema::table('admin_users', function (Blueprint $table): void {
                $table->dropColumn('admin_type');
            });
        }

        Schema::dropIfExists('countries');
    }

    private function copyFromParent(string $child, string $foreignKey, string $parent): void
    {
        if (! $this->canCopy($child, $foreignKey, $parent)) {
            return;
        }

        DB::table($child)
            ->join($parent, $child . '.' . $foreignKey, '=', $parent . '.id')
            ->whereNull($child . '.country_id')
            ->update([$child . '.country_id' => DB::raw($parent . '.country_id')]);
    }

    private function scopeGeographicUniqueKeys(): void
    {
        if (Schema::hasTable('regions')) {
            $this->dropUniqueIndexesForColumns('regions', ['name']);
            $this->addUniqueIndexIfMissing('regions', ['country_id', 'name'], 'regions_country_name_unique');
        }

        if (Schema::hasTable('places')) {
            $this->dropUniqueIndexesForColumns('places', ['place_name']);
            $this->addUniqueIndexIfMissing('places', ['country_id', 'place_name'], 'places_country_place_name_unique');
        }
    }

    private function backfillTransportRouteIdentity(): void
    {
        DB::table('transport_service_route_pairs')
            ->whereNull('route_identity')
            ->orderBy('id')
            ->chunkById(500, function ($routes): void {
                foreach ($routes as $route) {
                    DB::table('transport_service_route_pairs')
                        ->where('id', $route->id)
                        ->update([
                            'route_identity' => hash('sha256', json_encode([
                                (string) $route->service_type,
                                (string) $route->route_from,
                                (string) $route->route_to,
                            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
                        ]);
                }

            });
    }

    private function ensureCountryTableSupportsForeignKeys(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $table = DB::selectOne(
            'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            ['countries']
        );
        if ($table && strtoupper((string) $table->ENGINE) !== 'INNODB') {
            DB::statement('ALTER TABLE `countries` ENGINE=InnoDB');
        }
    }

    private function dropUniqueIndexesForColumns(string $table, array $columns): void
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['unique'] ?? false)
                && ! ($index['primary'] ?? false)
                && ($index['columns'] ?? []) === $columns) {
                $this->dropIndexIfPresent($table, $index['name']);
            }
        }
    }

    private function addUniqueIndexIfMissing(string $table, array $columns, string $name): void
    {
        if (! Schema::hasTable($table) || $this->uniqueIndexExistsForColumns($table, $columns)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $name): void {
            $blueprint->unique($columns, $name);
        });
    }

    private function uniqueIndexExistsForColumns(string $table, array $columns): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['unique'] ?? false) && ($index['columns'] ?? []) === $columns) {
                return true;
            }
        }

        return false;
    }

    private function dropIndexIfPresent(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName): void {
            $blueprint->dropIndex($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (strcasecmp($index['name'] ?? '', $indexName) === 0) {
                return true;
            }
        }

        return false;
    }

    private function foreignKeyExists(string $table, string $foreignKeyName): bool
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (strcasecmp($foreignKey['name'] ?? '', $foreignKeyName) === 0) {
                return true;
            }
        }

        return false;
    }

    private function copyFromOperator(string $table, int $fallbackCountryId): void
    {
        if (! $this->canCopy($table, 'operator_id', 'operators')) {
            return;
        }

        DB::table($table)
            ->join('operators', $table . '.operator_id', '=', 'operators.id')
            ->whereNull($table . '.country_id')
            ->update([$table . '.country_id' => DB::raw('operators.country_id')]);
        DB::table($table)->whereNull('country_id')->update(['country_id' => $fallbackCountryId]);
    }

    private function copyFromOperatorString(string $table, int $fallbackCountryId): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'country_id')
            || ! Schema::hasColumn($table, 'operator_id') || ! Schema::hasTable('operators')
            || ! Schema::hasColumn('operators', 'country_id')
            || ! Schema::hasColumn('operators', 'operator_id')) {
            return;
        }

        DB::table($table)
            ->join('operators', $table . '.operator_id', '=', 'operators.operator_id')
            ->whereNull($table . '.country_id')
            ->update([$table . '.country_id' => DB::raw('operators.country_id')]);
        DB::table($table)->whereNull('country_id')->update(['country_id' => $fallbackCountryId]);
    }

    private function copyProductCountry(string $table, $countryIds, int $fallbackCountryId): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'country_id')) {
            return;
        }

        if (Schema::hasColumn($table, 'country')) {
            foreach ($countryIds as $name => $countryId) {
                DB::table($table)
                    ->whereRaw('LOWER(TRIM(country)) = ?', [mb_strtolower(trim((string) $name))])
                    ->whereNull('country_id')
                    ->update(['country_id' => $countryId]);
            }
        }

        if (Schema::hasColumn($table, 'business_id') && Schema::hasTable('businesses')) {
            $this->copyFromParent($table, 'business_id', 'businesses');
        } elseif (Schema::hasColumn($table, 'operator_id') && Schema::hasTable('operators')) {
            $this->copyFromOperator($table, $fallbackCountryId);
        }

        DB::table($table)->whereNull('country_id')->update(['country_id' => $fallbackCountryId]);
    }

    private function copyBookingItemCountries(): void
    {
        if (! Schema::hasTable('booking_line_items')) {
            return;
        }

        $sources = [
            'accommodation' => 'accommodations',
            'activity' => 'activities',
            'transport' => 'transports',
            'package' => 'packages',
        ];
        foreach ($sources as $serviceType => $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::table('booking_line_items')
                ->join($table, 'booking_line_items.service_id', '=', $table . '.id')
                ->where('booking_line_items.service_type', $serviceType)
                ->whereNull('booking_line_items.country_id')
                ->update(['booking_line_items.country_id' => DB::raw($table . '.country_id')]);
        }
    }

    private function copyBookingCountries(): void
    {
        if (! Schema::hasTable('bookings') || ! Schema::hasTable('booking_line_items')) {
            return;
        }

        $countryGroups = DB::table('booking_line_items')
            ->select('booking_id', DB::raw('MIN(country_id) AS country_id'), DB::raw('COUNT(DISTINCT country_id) AS country_count'), DB::raw('COUNT(*) AS item_count'), DB::raw('COUNT(country_id) AS country_item_count'))
            ->groupBy('booking_id')
            ->get();
        foreach ($countryGroups as $group) {
            if ((int) $group->country_count === 1 && (int) $group->country_item_count === (int) $group->item_count) {
                DB::table('bookings')->where('id', $group->booking_id)->update(['country_id' => $group->country_id]);
            }
        }
    }

    private function copyBookingReferenceCountries(): void
    {
        if (! Schema::hasTable('booking_refs') || ! Schema::hasTable('bookings')) {
            return;
        }

        $countryGroups = DB::table('bookings')
            ->whereNotNull('booking_ref_id')
            ->select('booking_ref_id', DB::raw('MIN(country_id) AS country_id'), DB::raw('COUNT(DISTINCT country_id) AS country_count'), DB::raw('COUNT(*) AS booking_count'), DB::raw('COUNT(country_id) AS country_booking_count'))
            ->groupBy('booking_ref_id')
            ->get();
        foreach ($countryGroups as $group) {
            if ((int) $group->country_count === 1 && (int) $group->country_booking_count === (int) $group->booking_count) {
                DB::table('booking_refs')->where('id', $group->booking_ref_id)->update(['country_id' => $group->country_id]);
            }
        }
    }

    private function copyPaymentCountries(): void
    {
        if (! Schema::hasTable('payment_transactions')) {
            return;
        }

        if (Schema::hasColumn('payment_transactions', 'booking_id') && Schema::hasTable('bookings')) {
            $this->copyFromParent('payment_transactions', 'booking_id', 'bookings');
        }

        if (Schema::hasColumn('payment_transactions', 'booking_ref_id') && Schema::hasTable('booking_refs')) {
            DB::table('payment_transactions')
                ->join('booking_refs', 'payment_transactions.booking_ref_id', '=', 'booking_refs.id')
                ->whereNull('payment_transactions.country_id')
                ->update(['payment_transactions.country_id' => DB::raw('booking_refs.country_id')]);
        }
    }

    private function copyGuestCountries(): void
    {
        if (! Schema::hasTable('booking_guests')) {
            return;
        }

        foreach ([
            'accommodation' => 'accommodation_bookings',
            'activity' => 'activity_bookings',
            'transport' => 'transport_bookings',
        ] as $bookingType => $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            DB::table('booking_guests')
                ->join($table, 'booking_guests.booking_id', '=', $table . '.id')
                ->where('booking_guests.booking_type', $bookingType)
                ->whereNull('booking_guests.country_id')
                ->update(['booking_guests.country_id' => DB::raw($table . '.country_id')]);
        }
    }

    private function copyScheduleChangeCountries(): void
    {
        if (! Schema::hasTable('booking_schedule_changes')) {
            return;
        }
        foreach ([
            'accommodation' => 'accommodation_bookings',
            'activity' => 'activity_bookings',
            'transport' => 'transport_bookings',
        ] as $serviceType => $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            DB::table('booking_schedule_changes')
                ->join($table, 'booking_schedule_changes.service_booking_id', '=', $table . '.id')
                ->where('booking_schedule_changes.service_type', $serviceType)
                ->whereNull('booking_schedule_changes.country_id')
                ->update(['booking_schedule_changes.country_id' => DB::raw($table . '.country_id')]);
        }
    }

    private function copyDeadlineCountries(): void
    {
        if (! Schema::hasTable('trip_action_deadlines')) {
            return;
        }
        foreach ([
            'accommodation' => 'accommodation_bookings',
            'activity' => 'activity_bookings',
            'transport' => 'transport_bookings',
        ] as $serviceType => $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            DB::table('trip_action_deadlines')
                ->join($table, 'trip_action_deadlines.service_booking_id', '=', $table . '.id')
                ->where('trip_action_deadlines.service_type', $serviceType)
                ->whereNull('trip_action_deadlines.country_id')
                ->update(['trip_action_deadlines.country_id' => DB::raw($table . '.country_id')]);
        }
    }

    private function fillCountry(string $table, int $countryId): void
    {
        if (Schema::hasTable($table) && Schema::hasColumn($table, 'country_id')) {
            DB::table($table)->whereNull('country_id')->update(['country_id' => $countryId]);
        }
    }

    private function canCopy(string $child, string $foreignKey, string $parent): bool
    {
        return Schema::hasTable($child)
            && Schema::hasTable($parent)
            && Schema::hasColumn($child, 'country_id')
            && Schema::hasColumn($child, $foreignKey)
            && Schema::hasColumn($parent, 'country_id');
    }
};
