<?php

namespace App\Support;

use App\Models\Group;
use Carbon\Carbon;

class ClosedGroupBookingDates
{
    public static function durationDays(Group $group): int
    {
        $duration = (int) ($group->no_of_days ?? 0);
        if ($duration > 0) {
            return $duration;
        }

        $dayIndexes = array_filter(
            array_keys((array) ($group->itinerary ?? [])),
            static fn ($index): bool => is_numeric($index) && (int) $index >= 0
        );

        return max(1, count($dayIndexes));
    }

    public static function earliestBookableStartDate(Group $group, ?Carbon $today = null): ?Carbon
    {
        if (!$group->available_from || !$group->available_to) {
            return null;
        }

        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $earliest = Carbon::parse($group->available_from)->startOfDay();
        $cutoffDate = $today->addDays(max(0, (int) ($group->booking_cutoff_days ?? 0)));

        return $earliest->greaterThan($cutoffDate) ? $earliest : $cutoffDate;
    }

    public static function latestBookableStartDate(Group $group): ?Carbon
    {
        if (!$group->available_to) {
            return null;
        }

        return Carbon::parse($group->available_to)
            ->startOfDay()
            ->subDays(self::durationDays($group) - 1);
    }

    public static function hasBookableStartDate(Group $group, ?Carbon $today = null): bool
    {
        $earliest = self::earliestBookableStartDate($group, $today);
        $latest = self::latestBookableStartDate($group);

        return $earliest !== null && $latest !== null && $earliest->lessThanOrEqualTo($latest);
    }

    public static function isBookableStartDate(Group $group, string $startDate, ?Carbon $today = null): bool
    {
        if (!self::hasBookableStartDate($group, $today)) {
            return false;
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $earliest = self::earliestBookableStartDate($group, $today);
        $latest = self::latestBookableStartDate($group);

        return $start->greaterThanOrEqualTo($earliest) && $start->lessThanOrEqualTo($latest);
    }
}
