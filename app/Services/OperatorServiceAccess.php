<?php

namespace App\Services;

use App\Models\Operator;
use App\Models\OperatorProfile;
use App\Models\OperatorUser;

class OperatorServiceAccess
{
    public const SERVICE_MODULES = [
        'Accommodation' => 'accommodation',
        'Activity' => 'activity',
        'Transport' => 'transport',
    ];

    public function selectedServices($user): array
    {
        if (! $user instanceof Operator && ! $user instanceof OperatorUser) {
            return [];
        }

        $profile = null;
        if (filled($user->business_id)) {
            $profile = OperatorProfile::query()
                ->where('business_id', $user->business_id)
                ->first();
        }

        if (! $profile && filled($user->operator_id)) {
            $profile = OperatorProfile::query()
                ->where('operator_id', $user->operator_id)
                ->first();
        }

        $serviceTypes = $profile?->service_types;
        if (is_string($serviceTypes)) {
            $serviceTypes = json_decode($serviceTypes, true);
        }

        if (! is_array($serviceTypes)) {
            return [];
        }

        $selected = [];
        foreach ($serviceTypes as $serviceType) {
            if (! is_string($serviceType)) {
                continue;
            }

            foreach (array_keys(self::SERVICE_MODULES) as $supportedService) {
                if (strcasecmp(trim($serviceType), $supportedService) === 0) {
                    $selected[] = $supportedService;
                    break;
                }
            }
        }

        return array_values(array_unique($selected));
    }

    public function serviceForRouteName(?string $routeName, array $routeParameters = []): ?string
    {
        $serviceType = $routeParameters['serviceType'] ?? null;
        if (is_string($serviceType)) {
            $service = ucfirst(strtolower($serviceType));
            if (isset(self::SERVICE_MODULES[$service])) {
                return $service;
            }
        }

        if ($routeName === 'operator.bookings.postpone.activity-slots') {
            return 'Activity';
        }

        foreach (self::SERVICE_MODULES as $service => $routePrefix) {
            if ($routeName && str_starts_with($routeName, 'operator.' . $routePrefix . '.')) {
                return $service;
            }
        }

        if ($routeName && str_starts_with($routeName, 'operator.drivers.')) {
            return 'Transport';
        }

        return null;
    }

    public function allows($user, string $service): bool
    {
        return in_array($service, $this->selectedServices($user), true);
    }
}
