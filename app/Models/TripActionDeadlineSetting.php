<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripActionDeadlineSetting extends Model
{
    protected $table = 'trip_action_deadline_settings';
    protected $primaryKey = 'action_type';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public const DEFAULTS = [
        'guest_details' => 24,
        'driver_license_expire' => 24,
        'driver_assign' => 24,
        'vehicle_assign' => 24,
    ];

    public static function intervals(): array
    {
        $stored = self::query()->pluck('interval_hours', 'action_type')->all();

        return array_replace(self::DEFAULTS, array_map('intval', $stored));
    }

    public static function saveIntervals(array $intervals, ?int $adminId): void
    {
        foreach ($intervals as $actionType => $hours) {
            self::query()->updateOrCreate(
                ['action_type' => $actionType],
                ['interval_hours' => $hours, 'updated_by_admin_id' => $adminId]
            );
        }
    }
}
