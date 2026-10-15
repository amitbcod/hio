<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopesToAdminCountry;

class TransportServiceRoutePair extends Model
{
    use ScopesToAdminCountry;

    protected $table = 'transport_service_route_pairs';

    protected static function booted(): void
    {
        static::saving(function (self $routePair): void {
            $routePair->route_identity = self::identityFor(
                (string) $routePair->service_type,
                (string) $routePair->route_from,
                (string) $routePair->route_to
            );
        });
    }

    public static function identityFor(string $serviceType, string $routeFrom, string $routeTo): string
    {
        return hash('sha256', json_encode([
            $serviceType,
            $routeFrom,
            $routeTo,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    protected $fillable = [
        'service_type',
        'country_id',
        'route_from',
        'route_to',
        'is_active',
        'trip_time_minutes',
        'buffer_time_minutes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
