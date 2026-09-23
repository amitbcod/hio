<?php

namespace App\Services;

use App\Models\Transport;
use App\Models\TransportRoute;
use Carbon\Carbon;
use InvalidArgumentException;

class TransportPricingService
{
    public function calculateOneWay(float $arrivalRate, float $departureRate, bool $reverseDirection = false): array
    {
        $rate = $reverseDirection ? $departureRate : $arrivalRate;

        return [
            'arrival_rate' => round(max(0, $arrivalRate), 2),
            'departure_rate' => round(max(0, $departureRate), 2),
            'discount_percentage' => 0.0,
            'discount_amount' => 0.0,
            'final_price' => round(max(0, $rate), 2),
        ];
    }

    public function calculateTwoWay(float $arrivalRate, float $departureRate, float $discountPercentage): array
    {
        $arrivalRate = round(max(0, $arrivalRate), 2);
        $departureRate = round(max(0, $departureRate), 2);
        $discountPercentage = min(100, max(0, $discountPercentage));
        $combined = round($arrivalRate + $departureRate, 2);
        $discountAmount = round($combined * $discountPercentage / 100, 2);

        return [
            'arrival_rate' => $arrivalRate,
            'departure_rate' => $departureRate,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'final_price' => round(max(0, $combined - $discountAmount), 2),
        ];
    }

    public function calculateReturnBookingPrices(float $outboundPrice, float $returnPrice, float $discountPercentage): array
    {
        $outboundPrice = round(max(0, $outboundPrice), 2);
        $returnPrice = round(max(0, $returnPrice), 2);
        $discountPercentage = min(100, max(0, $discountPercentage));
        $outboundDiscount = round($outboundPrice * $discountPercentage / 100, 2);
        $returnDiscount = round($returnPrice * $discountPercentage / 100, 2);

        return [
            'outbound' => [
                'base_price' => $outboundPrice,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $outboundDiscount,
                'final_price' => round(max(0, $outboundPrice - $outboundDiscount), 2),
            ],
            'return' => [
                'base_price' => $returnPrice,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $returnDiscount,
                'final_price' => round(max(0, $returnPrice - $returnDiscount), 2),
            ],
            'combined_total' => round(($outboundPrice - $outboundDiscount) + ($returnPrice - $returnDiscount), 2),
        ];
    }

    public function resolveForBooking(
        Transport $transport,
        ?string $routeId,
        string $from,
        string $to,
        ?string $pickupDate = null,
        bool $twoWay = false,
        string $mode = 'direct',
        ?string $departureDate = null
    ): array {
        $route = $this->findRoute($transport, $routeId, $from, $to);
        if (!$route) {
            throw new InvalidArgumentException('The selected transport route is no longer available.');
        }

        $pricing = is_array($route->pricing) ? $route->pricing : [];
        $isReverse = $this->isReverseDirection($route, $from, $to);
        $arrivalField = $mode === 'package' ? 'package_price' : ($mode === 'group' ? 'group_price' : 'default_price');
        $departureField = $mode === 'package' ? 'package_departure_price' : ($mode === 'group' ? 'group_departure_price' : 'departure_price');
        $arrivalSeasonal = $this->seasonalRate($pricing['seasonal'] ?? [], $pickupDate, $arrivalField, 'price');
        $departureSeasonal = $this->seasonalRate($pricing['seasonal'] ?? [], $departureDate ?? $pickupDate, 'price', $departureField);

        $arrivalRate = $arrivalSeasonal['arrival'];
        $departureRate = $departureSeasonal['departure'];
        if ($arrivalRate === null) {
            $arrivalRate = $this->numericValue($pricing[$arrivalField] ?? null);
        }
        if ($departureRate === null) {
            $departureRate = $this->numericValue($pricing[$departureField] ?? null);
        }

        $arrivalRate ??= 0.0;
        $departureRate ??= 0.0;
        $discount = (float) ($transport->return_discount_percentage ?? 0);
        $result = $twoWay
            ? $this->calculateTwoWay($arrivalRate, $departureRate, $discount)
            : $this->calculateOneWay($arrivalRate, $departureRate, $isReverse);

        return array_merge($result, [
            'route_id' => $route->route_id,
            'route_from' => $route->route_from ?? $route->pickup_value,
            'route_to' => $route->route_to ?? $route->dropoff_value,
            'requested_from' => $from,
            'requested_to' => $to,
            'reverse_direction' => $isReverse,
            'two_way' => $twoWay,
            'mode' => $mode,
        ]);
    }

    public function findRoute(Transport $transport, ?string $routeId, string $from, string $to): ?TransportRoute
    {
        $routes = $transport->routes()->get();
        $normalizedFrom = $this->normalize($from);
        $normalizedTo = $this->normalize($to);

        return $routes->first(function (TransportRoute $route) use ($routeId, $normalizedFrom, $normalizedTo) {
            if ($routeId !== null && $routeId !== '' && ((string) $route->route_id === (string) $routeId || (string) $route->id === (string) $routeId)) {
                return true;
            }

            return $this->normalize($route->route_from ?? $route->pickup_value) === $normalizedFrom
                && $this->normalize($route->route_to ?? $route->dropoff_value) === $normalizedTo;
        }) ?: $routes->first(function (TransportRoute $route) use ($normalizedFrom, $normalizedTo) {
            return $this->normalize($route->route_from ?? $route->pickup_value) === $normalizedTo
                && $this->normalize($route->route_to ?? $route->dropoff_value) === $normalizedFrom;
        });
    }

    private function isReverseDirection(TransportRoute $route, string $from, string $to): bool
    {
        return $this->normalize($route->route_from ?? $route->pickup_value) === $this->normalize($to)
            && $this->normalize($route->route_to ?? $route->dropoff_value) === $this->normalize($from);
    }

    private function seasonalRate(array $entries, ?string $date, string $arrivalField, string $departureField): array
    {
        if (blank($date)) {
            return ['arrival' => null, 'departure' => null];
        }

        $target = Carbon::parse($date)->startOfDay();
        foreach ($entries as $entry) {
            if (!is_array($entry) || blank($entry['start'] ?? $entry['start_date'] ?? null) || blank($entry['end'] ?? $entry['end_date'] ?? null)) {
                continue;
            }

            $start = Carbon::parse($entry['start'] ?? $entry['start_date'])->startOfDay();
            $end = Carbon::parse($entry['end'] ?? $entry['end_date'])->endOfDay();
            if ($target->between($start, $end, true)) {
                return [
                    'arrival' => $this->numericValue($entry['price'] ?? $entry[$arrivalField] ?? null),
                    'departure' => $this->numericValue($entry['departure_price'] ?? $entry[$departureField] ?? null),
                ];
            }
        }

        return ['arrival' => null, 'departure' => null];
    }

    private function numericValue($value): ?float
    {
        return is_numeric($value) ? round(max(0, (float) $value), 2) : null;
    }

    private function normalize($value): string
    {
        return strtolower(trim((string) $value));
    }
}
