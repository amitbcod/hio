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

        return $this->normalizeServiceTypes($profile?->service_types ?? []);
    }

    private function normalizeServiceTypes(mixed $serviceTypes): array
    {
        $values = $serviceTypes;

        if ($values === null) {
            return [];
        }

        if (is_string($values)) {
            $trimmed = trim($values);
            if ($trimmed === '') {
                return [];
            }

            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                $values = $decoded;
            } else {
                $values = preg_split('/\s*,\s*/', $trimmed, -1, PREG_SPLIT_NO_EMPTY) ?: [$trimmed];
            }
        }

        if (! is_array($values)) {
            $values = [$values];
        }

        $selected = [];
        foreach ($values as $serviceType) {
            if (is_array($serviceType)) {
                $selected = [...$selected, ...$this->normalizeServiceTypes($serviceType)];
                continue;
            }

            if (! is_string($serviceType) && ! is_numeric($serviceType)) {
                continue;
            }

            $serviceName = trim((string) $serviceType);
            if ($serviceName === '') {
                continue;
            }

            foreach (array_keys(self::SERVICE_MODULES) as $supportedService) {
                if (strcasecmp($serviceName, $supportedService) === 0) {
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
