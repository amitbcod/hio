<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\CountryAccess;

class TripActionDeadlineSetting extends Model
{
    protected $table = 'trip_action_deadline_settings';
    protected $guarded = [];

    public const DEFAULTS = [
        'guest_details' => 24,
        'driver_license_expire' => 24,
        'driver_assign' => 24,
        'vehicle_assign' => 24,
    ];

    public static function intervals(?int $countryId = null): array
    {
        $countryId ??= CountryAccess::countryId() ?? Country::query()
            ->where('iso_code', 'MU')
            ->value('id');
        $stored = self::query()
            ->where('country_id', $countryId)
            ->pluck('interval_hours', 'action_type')
            ->all();

        return array_replace(self::DEFAULTS, array_map('intval', $stored));
    }

    public static function saveIntervals(array $intervals, ?int $adminId, int $countryId): void
    {
        foreach ($intervals as $actionType => $hours) {
            self::query()->updateOrCreate(
                ['action_type' => $actionType, 'country_id' => $countryId],
                ['interval_hours' => $hours, 'updated_by_admin_id' => $adminId]
            );
        }
    }
}
