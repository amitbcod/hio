<?php

namespace App\Services;

use App\Models\Transport;

class GroupTransportRouteResolver
{
    public function hasExplicitSelection(array $dayEntry): bool
    {
        foreach ((array) ($dayEntry['transport_schedule'] ?? []) as $serviceGroup) {
            if (!is_array($serviceGroup)) {
                continue;
            }
            foreach ($serviceGroup as $routeData) {
                $selected = is_array($routeData)
                    ? (!empty($routeData['selected']) || !empty($routeData['selected_route']) || !empty($routeData['route_id']))
                    : ($routeData !== null && $routeData !== false && $routeData !== '');
                if ($selected) {
                    return true;
                }
            }
        }

        return false;
    }

    public function selectedLegs(Transport $transport, array $dayEntry): array
    {
        $legs = [];
        $hasExplicitSelections = false;
        $routes = $transport->relationLoaded('routes') ? $transport->routes : $transport->routes()->get();

        foreach ((array) ($dayEntry['transport_schedule'] ?? []) as $serviceGroup) {
            if (!is_array($serviceGroup)) {
                continue;
            }

            foreach ($serviceGroup as $routeKey => $routeData) {
                $selected = is_array($routeData)
                    ? (!empty($routeData['selected']) || !empty($routeData['selected_route']) || !empty($routeData['route_id']))
                    : ($routeData !== null && $routeData !== false && $routeData !== '');
                if (!$selected) {
                    continue;
                }
                $hasExplicitSelections = true;

                $reverse = is_string($routeKey) && preg_match('/-rev$/i', $routeKey) === 1;
                $routeId = is_array($routeData)
                    ? ($routeData['route_id'] ?? $routeData['selected_route'] ?? $routeData['id'] ?? null)
                    : null;
                $routeId ??= is_string($routeKey) ? preg_replace('/-(fwd|rev)$/i', '', $routeKey) : $routeKey;

                $route = $routes->first(fn ($candidate) =>
                    (string) ($candidate->route_id ?? '') === (string) $routeId
                    || (string) ($candidate->id ?? '') === (string) $routeId
                );
                if (!$route) {
                    continue;
                }

                $from = $reverse
                    ? ($route->route_to ?? $route->dropoff_value ?? '')
                    : ($route->route_from ?? $route->pickup_value ?? '');
                $to = $reverse
                    ? ($route->route_from ?? $route->pickup_value ?? '')
                    : ($route->route_to ?? $route->dropoff_value ?? '');
                if (trim((string) $from) === '' || trim((string) $to) === '') {
                    continue;
                }

                $legs[] = [
                    'route_id' => $route->route_id ?? $route->id,
                    'route_from' => trim((string) $from),
                    'route_to' => trim((string) $to),
                    'direction' => $reverse ? 'reverse' : 'forward',
                ];
            }
        }

        if ($hasExplicitSelections) {
            return $legs;
        }

        $from = trim((string) ($dayEntry['route_from'] ?? ''));
        $to = trim((string) ($dayEntry['route_to'] ?? ''));
        if ($from !== '' && $to !== '') {
            return [['route_id' => null, 'route_from' => $from, 'route_to' => $to, 'direction' => 'forward']];
        }

        return [];
    }

    public function label(Transport $transport, array $dayEntry): string
    {
        $labels = array_map(
            fn (array $leg) => $leg['route_from'] . ' → ' . $leg['route_to'],
            $this->selectedLegs($transport, $dayEntry)
        );

        return implode(', ', array_values(array_unique($labels)));
    }
}