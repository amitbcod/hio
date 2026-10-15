<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CountryOwnership
{
    public static function refreshBooking(int $bookingId): void
    {
        if (! Schema::hasColumn('bookings', 'country_id')
            || ! Schema::hasColumn('booking_line_items', 'country_id')) {
            return;
        }
        $countryId = self::singleCountryForBooking($bookingId);
        DB::table('bookings')->where('id', $bookingId)->update(['country_id' => $countryId]);

        $bookingRefId = DB::table('bookings')->where('id', $bookingId)->value('booking_ref_id');
        if ($bookingRefId) {
            DB::table('booking_refs')->where('id', $bookingRefId)->update([
                'country_id' => self::singleCountryForReference((int) $bookingRefId),
            ]);
        }
    }

    public static function resolve(Model $model): ?int
    {
        $table = $model->getTable();
        if (! Schema::hasColumn($table, 'country_id')) {
            return null;
        }

        if ($table === 'businesses') {
            return self::countryForName((string) $model->getAttribute('country'));
        }
        if ($table === 'operators') {
            return self::parentCountry('businesses', $model->getAttribute('business_id'));
        }
        if ($table === 'accommodations') {
            return self::countryForName((string) $model->getAttribute('country'))
                ?? self::parentCountry('businesses', $model->getAttribute('business_id'))
                ?? self::countryForOperator($model->getAttribute('operator_id'));
        }
        if (in_array($table, ['activities', 'transports'], true)) {
            return self::countryForOperator($model->getAttribute('operator_id'));
        }
        if ($table === 'accommodation_bookings') {
            return self::parentCountry('accommodations', $model->getAttribute('accommodation_id'));
        }
        if ($table === 'activity_bookings') {
            return self::parentCountry('activities', $model->getAttribute('activity_id'));
        }
        if ($table === 'transport_bookings') {
            return self::parentCountry('transports', $model->getAttribute('transport_id'));
        }
        if ($table === 'booking_line_items') {
            $productTable = [
                'accommodation' => 'accommodations',
                'activity' => 'activities',
                'transport' => 'transports',
                'package' => 'packages',
            ][$model->getAttribute('service_type') ?? ''] ?? null;

            return $productTable
                ? self::parentCountry($productTable, $model->getAttribute('service_id'))
                : null;
        }
        if ($table === 'bookings') {
            return self::singleCountryForBooking((int) $model->getAttribute('id'));
        }
        if ($table === 'booking_refs') {
            return self::singleCountryForReference((int) $model->getAttribute('id'));
        }
        if ($table === 'payment_transactions') {
            return self::parentCountry('bookings', $model->getAttribute('booking_id'))
                ?? self::parentCountry('booking_refs', $model->getAttribute('booking_ref_id'));
        }
        if ($table === 'booking_guests') {
            $parent = [
                'accommodation' => 'accommodation_bookings',
                'activity' => 'activity_bookings',
                'transport' => 'transport_bookings',
            ][$model->getAttribute('booking_type') ?? ''] ?? null;

            return $parent
                ? self::parentCountry($parent, $model->getAttribute('booking_id'))
                : null;
        }
        if (in_array($table, ['booking_schedule_changes', 'trip_action_deadlines'], true)) {
            $parent = [
                'accommodation' => 'accommodation_bookings',
                'activity' => 'activity_bookings',
                'transport' => 'transport_bookings',
            ][$model->getAttribute('service_type') ?? ''] ?? null;

            return $parent
                ? self::parentCountry($parent, $model->getAttribute('service_booking_id'))
                : null;
        }
        $serviceParent = self::serviceChildParent($table);
        if ($serviceParent !== null) {
            return self::parentCountry($serviceParent[0], $model->getAttribute($serviceParent[1]));
        }
        if (in_array($table, [
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
            'operator_drivers',
        ], true)) {
            $countryId = self::parentCountry('businesses', $model->getAttribute('business_id'));
            if ($countryId !== null) {
                return $countryId;
            }
            $operatorId = $model->getAttribute('operator_id');
            if ($operatorId) {
                return DB::table('operators')
                    ->where('operator_id', $operatorId)
                    ->orWhere('id', $operatorId)
                    ->value('country_id');
            }
        }

        return null;
    }

    public static function assertItineraryCountry(array $itinerary, int $countryId): void
    {
        $serviceTables = [
            'accommodation' => 'accommodations',
            'activity' => 'activities',
            'transport' => 'transports',
        ];
        $errors = [];

        foreach ($itinerary as $dayIndex => $day) {
            if (! is_array($day)) {
                $errors["itinerary.{$dayIndex}"] = 'Each itinerary day must be a valid object.';
                continue;
            }

            $accommodationId = $day['accommodation'] ?? null;
            foreach ($serviceTables as $field => $table) {
                $serviceId = $day[$field] ?? null;
                if ($serviceId === null || $serviceId === '') {
                    continue;
                }

                if (! is_scalar($serviceId) || ! ctype_digit((string) $serviceId)
                    || ! DB::table($table)->where('id', (int) $serviceId)
                        ->where('country_id', $countryId)->exists()) {
                    $errors["itinerary.{$dayIndex}.{$field}"] = 'The selected service must belong to this country.';
                }
            }

            $roomIds = $day['rooms'] ?? [];
            if (! is_array($roomIds) || $roomIds === []) {
                continue;
            }
            if (array_filter($roomIds, fn ($roomId) => ! is_scalar($roomId)
                || ! ctype_digit((string) $roomId)) !== []) {
                $errors["itinerary.{$dayIndex}.rooms"] = 'Selected rooms must be valid room IDs.';
                continue;
            }
            if (! is_scalar($accommodationId) || ! ctype_digit((string) $accommodationId)) {
                $errors["itinerary.{$dayIndex}.rooms"] = 'Rooms must belong to the selected accommodation.';
                continue;
            }

            $validRoomCount = DB::table('accommodation_rooms')
                ->where('country_id', $countryId)
                ->where('accommodation_id', (int) $accommodationId)
                ->whereIn('id', $roomIds)
                ->distinct()
                ->count('id');
            if ($validRoomCount !== count(array_unique($roomIds))) {
                $errors["itinerary.{$dayIndex}.rooms"] = 'Selected rooms must belong to this country and accommodation.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private static function serviceChildParent(string $table): ?array
    {
        if (in_array($table, [
            'accommodation_rooms',
            'accommodation_rates',
            'accommodation_media',
            'accommodation_inventory',
            'accommodation_compliance',
            'accommodation_promotions',
            'accommodation_fees',
        ], true)) {
            return ['accommodations', 'accommodation_id'];
        }
        if (in_array($table, [
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
        ], true)) {
            return ['activities', 'activity_id'];
        }
        if (in_array($table, ['transport_rates', 'transport_routes', 'transport_vehicles'], true)) {
            return ['transports', 'transport_id'];
        }
        if ($table === 'transport_booking_assignments') {
            return ['transport_bookings', 'transport_booking_id'];
        }

        return null;
    }

    private static function countryForName(string $name): ?int
    {
        if (trim($name) === '' || ! Schema::hasTable('countries')) {
            return null;
        }

        return DB::table('countries')
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($name))])
            ->value('id');
    }

    private static function countryForOperator(mixed $operatorId): ?int
    {
        if (! $operatorId || ! Schema::hasColumn('operators', 'country_id')) {
            return null;
        }

        return DB::table('operators')
            ->where('id', $operatorId)
            ->value('country_id');
    }

    private static function parentCountry(string $table, mixed $id): ?int
    {
        if (! $id || ! Schema::hasTable($table) || ! Schema::hasColumn($table, 'country_id')) {
            return null;
        }

        return DB::table($table)->where('id', $id)->value('country_id');
    }

    private static function singleCountryForBooking(int $bookingId): ?int
    {
        if (! $bookingId || ! Schema::hasColumn('booking_line_items', 'country_id')
            || ! DB::table('booking_line_items')->where('booking_id', $bookingId)->exists()) {
            return null;
        }

        $countries = DB::table('booking_line_items')
            ->where('booking_id', $bookingId)
            ->distinct()
            ->pluck('country_id');

        return $countries->count() === 1 && $countries->first() !== null
            ? (int) $countries->first()
            : null;
    }

    private static function singleCountryForReference(int $bookingRefId): ?int
    {
        if (! $bookingRefId || ! Schema::hasColumn('bookings', 'country_id')
            || ! DB::table('bookings')->where('booking_ref_id', $bookingRefId)->exists()) {
            return null;
        }

        $countries = DB::table('bookings')
            ->where('booking_ref_id', $bookingRefId)
            ->distinct()
            ->pluck('country_id');

        return $countries->count() === 1 && $countries->first() !== null
            ? (int) $countries->first()
            : null;
    }
}
