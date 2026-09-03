<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Accommodation;
use App\Models\Activity;
use App\Models\Transport;
use Illuminate\Support\Facades\Log;

class PackagePricingService
{
    public function calculatePackageTotal(Package $package, int $adults = 2, int $children = 0, int $infants = 0): float
    {
        $breakdown = $this->calculatePackageTotalDetailed($package, $adults, $children, $infants);

        Log::info('Package pricing breakdown', [
            'package_id' => $package->id,
            'package_name' => $package->name,
            'adults' => $adults,
            'children' => $children,
            'infants' => $infants,
            'total' => $breakdown['total'],
            'items' => $breakdown['items'],
        ]);

        return (float) $breakdown['total'];
    }

    public function calculatePackageTotalDetailed(Package $package, int $adults = 2, int $children = 0, int $infants = 0): array
    {
        $itinerary = is_array($package->itinerary ?? null) ? $package->itinerary : [];
        $guestCount = max(1, $adults + $children + $infants);
        $total = 0.0;
        $items = [];

        foreach ($itinerary as $dayIndex => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $dayNumber = (int) $dayIndex + 1;
            $accommodation = !empty($entry['accommodation']) ? Accommodation::with('rooms')->find((int) $entry['accommodation']) : null;
            $activity = !empty($entry['activity']) ? Activity::find((int) $entry['activity']) : null;
            $transport = !empty($entry['transport']) ? Transport::with('routes')->find((int) $entry['transport']) : null;

            if ($accommodation) {
                $amount = $this->resolvePackageAccommodationAmount($accommodation, $entry, $package, $adults, $children, $infants);
                $total += $amount;
                $items[] = [
                    'day' => $dayNumber,
                    'type' => 'Accommodation',
                    'name' => $this->buildAccommodationLabel($accommodation, $entry, $adults, $children, $infants),
                    'amount' => round($amount, 2),
                ];
            }

            if ($activity) {
                $amount = $this->resolvePackageActivityAmount($activity, $entry, $guestCount, $package, $adults, $children, $infants);
                $total += $amount;
                $items[] = [
                    'day' => $dayNumber,
                    'type' => 'Activity',
                    'name' => $this->buildActivityLabel($activity, $entry),
                    'amount' => round($amount, 2),
                ];
            }

            if ($transport) {
                $amount = $this->resolvePackageTransportAmount($transport, $entry, $guestCount, $package);
                $total += $amount;
                $items[] = [
                    'day' => $dayNumber,
                    'type' => 'Transport',
                    'name' => $this->buildTransportLabel($transport, $entry),
                    'amount' => round($amount, 2),
                ];
            }
        }

        return [
            'total' => round($total, 2),
            'items' => $items,
        ];
    }

    private function roomMatchesGuestRequirements($room, int $adults, int $children = 0, int $infants = 0): bool
    {
        if (!$room) {
            return false;
        }

        $roomAdults = max(0, (int) ($room->capacity ?? 0));
        $roomChildren = max(0, (int) ($room->children_capacity ?? 0));
        $roomInfants = max(0, (int) ($room->infant_capacity ?? 0));
        $roomMaxPersons = max(0, (int) ($room->max_person_capacity ?? ($roomAdults + $roomChildren + max(0, $roomInfants - 1))));
        $totalGuests = $adults + $children + $infants;

        return $roomAdults >= $adults
            && $roomChildren >= $children
            && $roomInfants >= $infants
            && $roomMaxPersons >= $totalGuests;
    }

    private function selectPreferredRoomIds(Accommodation $accommodation, array $entry, int $adults = 2, int $children = 0, int $infants = 0): array
    {
        $roomIds = array_values(array_filter(array_map('intval', (array) ($entry['rooms'] ?? []))));
        if (empty($roomIds)) {
            $roomIds = \App\Models\AccommodationRoom::where('accommodation_id', $accommodation->id)->pluck('id')->all();
        }

        $rooms = \App\Models\AccommodationRoom::whereIn('id', $roomIds)->get();
        $matchingRoomIds = $rooms
            ->filter(fn ($room) => $this->roomMatchesGuestRequirements($room, $adults, $children, $infants))
            ->sortByDesc(function ($room) {
                $adultsCap = max(0, (int) ($room->capacity ?? 0));
                $childrenCap = max(0, (int) ($room->children_capacity ?? 0));
                $infantsCap = max(0, (int) ($room->infant_capacity ?? 0));
                $maxPersons = max(0, (int) ($room->max_person_capacity ?? ($adultsCap + $childrenCap + max(0, $infantsCap - 1))));
                return (($maxPersons * 1000) + ($adultsCap * 100) + ($childrenCap * 10) + $infantsCap);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (!empty($matchingRoomIds)) {
            return array_values(array_unique($matchingRoomIds));
        }

        $fallbackIds = $rooms
            ->sortByDesc(function ($room) {
                $adultsCap = max(0, (int) ($room->capacity ?? 0));
                $childrenCap = max(0, (int) ($room->children_capacity ?? 0));
                $infantsCap = max(0, (int) ($room->infant_capacity ?? 0));
                $maxPersons = max(0, (int) ($room->max_person_capacity ?? ($adultsCap + $childrenCap + max(0, $infantsCap - 1))));
                return (($maxPersons * 1000) + ($adultsCap * 100) + ($childrenCap * 10) + $infantsCap);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return array_values(array_unique($fallbackIds));
    }

    protected function buildAccommodationLabel(Accommodation $accommodation, array $entry, int $adults = 2, int $children = 0, int $infants = 0): string
    {
        $roomIds = $this->selectPreferredRoomIds($accommodation, $entry, $adults, $children, $infants);
        $roomNames = [];

        if (!empty($roomIds)) {
            $rooms = \App\Models\AccommodationRoom::whereIn('id', $roomIds)->get();
            foreach ($rooms as $room) {
                $roomNames[] = $room->room_name ?: ('Room #' . $room->id);
            }
        }

        if (!empty($roomNames)) {
            return $accommodation->name . ' - ' . implode(', ', $roomNames);
        }

        return $accommodation->name ?: 'Accommodation';
    }

    protected function buildActivityLabel(Activity $activity, array $entry): string
    {
        $selection = $entry['activity_selection'] ?? [];
        $selectionList = [];

        if (is_array($selection)) {
            $selectionList = array_filter(array_map('trim', $selection));
        }

        if (!empty($selectionList)) {
            return $activity->activity_name . ' - ' . implode(', ', $selectionList);
        }

        return $activity->activity_name ?: 'Activity';
    }

    protected function buildTransportLabel(Transport $transport, array $entry): string
    {
        $routes = $transport->routes ?? collect();
        $routeNames = [];

        foreach ($routes as $route) {
            $from = $route->route_from ?? '';
            $to = $route->route_to ?? '';
            $label = trim($from . ' ' . ($to ? '→ ' . $to : ''));
            if ($label !== '') {
                $routeNames[] = $label;
            }
        }

        if (!empty($routeNames)) {
            return $transport->name . ' - ' . implode(', ', array_slice($routeNames, 0, 2));
        }

        return $transport->name ?: 'Transport';
    }

      // Public wrappers so callers can obtain per-service amounts when needed
      public function getAccommodationAmount(\App\Models\Accommodation $accommodation, array $entry, ?Package $package = null, int $adults = 2, int $children = 0, int $infants = 0): float
      {
        return $this->resolvePackageAccommodationAmount($accommodation, $entry, $package, $adults, $children, $infants);
      }

      public function getActivityAmount(\App\Models\Activity $activity, array $entry, int $guestCount, ?Package $package = null): float
      {
        return $this->resolvePackageActivityAmount($activity, $entry, $guestCount, $package);
      }

      public function getTransportAmount(\App\Models\Transport $transport, array $entry, int $guestCount, ?Package $package = null): float
      {
        return $this->resolvePackageTransportAmount($transport, $entry, $guestCount, $package);
      }

    // Copied from TripController to preserve authoritative package pricing logic
    protected function resolvePackageAccommodationAmount(\App\Models\Accommodation $accommodation, array $entry, ?Package $package = null, int $adults = 2, int $children = 0, int $infants = 0): float
    {
      $roomIds = $this->selectPreferredRoomIds($accommodation, $entry, $adults, $children, $infants);

      $bestAmount = 0.0;
      $globalMode = $package && is_array($package->itinerary ?? null) ? ($package->itinerary['pricing_modes']['accommodation'] ?? 'discount_offer') : 'discount_offer';
      $globalDiscount = $package && is_array($package->itinerary ?? null) ? (float) ($package->itinerary['discounts']['accommodation'] ?? 20) : 20.0;
      $dayPricing = is_array($entry['pricing'] ?? null) ? $entry['pricing'] : [];

      foreach ($roomIds as $roomId) {
        $candidate = 0.0;

        $roomPricing = $dayPricing[$roomId] ?? [];
        $roomMode = $roomPricing['mode'] ?? $globalMode;
        $selectedPackage = $roomPricing['selected_package'] ?? null;
        $roomDiscount = is_numeric($roomPricing['discount_percent'] ?? null) ? (float) $roomPricing['discount_percent'] : $globalDiscount;

        if ($roomMode === 'package_rate') {
          if (!empty($selectedPackage)) {
            if (is_numeric($selectedPackage)) {
              $rate = \App\Models\AccommodationRate::find((int) $selectedPackage);
            } else {
              $rate = null;
            }
            if (empty($rate)) {
              $rate = \App\Models\AccommodationRate::where('accommodation_id', $accommodation->id)
                ->where('room_id', $roomId)
                ->where('rate_type', 'Package')
                ->orderByDesc('updated_at')
                ->first();
            }
            if ($rate) {
              $candidate = (float) ($rate->base_rate ?? $rate->final_rate ?? 0);
            }
          } else {
            $rate = \App\Models\AccommodationRate::where('accommodation_id', $accommodation->id)
              ->where('room_id', $roomId)
              ->where('rate_type', 'Package')
              ->orderByDesc('updated_at')
              ->first();
            if ($rate) {
              $candidate = (float) ($rate->base_rate ?? $rate->final_rate ?? 0);
            }
          }
        }

        if ($candidate <= 0) {
          $rate = \App\Models\AccommodationRate::where('accommodation_id', $accommodation->id)
            ->where('room_id', $roomId)
            ->where(function ($q) { $q->where('rate_type', '!=', 'Package')->orWhereNull('rate_type'); })
            ->where('is_rate_plan', false)
            ->orderByDesc('valid_from')
            ->first();

          if (!$rate) {
            $rate = \App\Models\AccommodationRate::where('accommodation_id', $accommodation->id)
              ->where('room_id', $roomId)
              ->where('is_rate_plan', false)
              ->orderByDesc('valid_from')
              ->first();
          }

          if ($rate) {
            $base = (float) ($rate->base_rate ?? $rate->final_rate ?? 0);
            if ($roomMode === 'discount_offer' && $roomDiscount > 0 && $roomDiscount <= 100) {
              $base = $base - ($base * $roomDiscount / 100.0);
            }
            $candidate = $base;
          }
        }

        if ($candidate > $bestAmount) {
          $bestAmount = $candidate;
        }
      }

      return round(max(0.0, $bestAmount), 2);
    }

    protected function resolvePackageActivityAmount(\App\Models\Activity $activity, array $entry, int $guestCount, ?Package $package = null, int $adults = 0, int $children = 0, int $infants = 0): float
    {
      $selection = $entry['activity_selection'] ?? [];
      if (!is_array($selection)) {
        $selection = $selection ? [$selection] : [];
      }

      $globalMode = $package && is_array($package->itinerary ?? null) ? ($package->itinerary['pricing_modes']['activity'] ?? 'discount_offer') : 'discount_offer';
      $globalDiscount = $package && is_array($package->itinerary ?? null) ? (float) ($package->itinerary['discounts']['activity'] ?? 10) : 10.0;

      $adultCount = max(0, $adults);
      $childCount = max(0, $children);
      $infantCount = max(0, $infants);

      if ($adultCount === 0 && $childCount === 0 && $infantCount === 0) {
        $adultCount = max(1, $guestCount);
      }

      $adultUnit = 0.0;
      $childUnit = 0.0;
      $infantUnit = 0.0;

      foreach (array_values(array_filter(array_map('trim', $selection))) as $selected) {
        $selectedAdult = 0.0;
        $selectedChild = 0.0;
        $selectedInfant = 0.0;

        if (!str_contains((string) $selected, '|')) {
          $variant = \App\Models\ActivityVariant::where('activity_id', $activity->id)->first();
          if ($variant) {
            $rates = \App\Models\ActivityRate::where('activity_id', $activity->id)
              ->where('variant_id', $variant->variant_id)
              ->orderByDesc('created_at')
              ->get();
            $rate = $rates->firstWhere('season', 'Package') ?: $rates->first();
            if ($rate) {
              $selectedAdult = (float) ($rate->adult_rate ?? $rate->base_rate ?? 0);
              $selectedChild = (float) ($rate->children_rate ?? $rate->adult_rate ?? $rate->base_rate ?? 0);
              $selectedInfant = (float) ($rate->infant_rate ?? 0);
            }
          }
        } else {
          [$variantId, $rateSpecificity] = array_pad(explode('|', (string) $selected, 2), 2, null);
          $variantId = trim((string) $variantId);
          $rateSpecificity = trim((string) ($rateSpecificity ?? ''));

          $baseQuery = \App\Models\ActivityRate::query()
            ->where('activity_id', $activity->id)
            ->where('variant_id', $variantId);

          if ($rateSpecificity !== '') {
            $baseQuery->where('rate_specificity', $rateSpecificity);
          }

          if ($globalMode === 'package_rate') {
            $rate = (clone $baseQuery)
              ->where('season', 'Package')
              ->orderByDesc('updated_at')
              ->first();
            if ($rate) {
              $selectedAdult = (float) ($rate->adult_rate ?? $rate->base_rate ?? 0);
              $selectedChild = (float) ($rate->children_rate ?? $rate->adult_rate ?? $rate->base_rate ?? 0);
              $selectedInfant = (float) ($rate->infant_rate ?? 0);
            }
          }

          if ($selectedAdult <= 0 && $selectedChild <= 0 && $selectedInfant <= 0) {
            $ratesCollection = (clone $baseQuery)
              ->where('season', '!=', 'Package')
              ->orderByDesc('created_at')
              ->get();

            if ($ratesCollection->isEmpty()) {
              $ratesCollection = (clone $baseQuery)
                ->orderByDesc('created_at')
                ->get();
            }

            if ($ratesCollection->isNotEmpty()) {
              $grouped = $ratesCollection->groupBy(function ($r) { return $r->season ?: 'One Season'; })
                ->map(function ($group) {
                    return $group->sortByDesc('updated_at')->first();
                })
                ->filter()
                ->values();
              $rate = $grouped->first();
              if ($rate) {
                $selectedAdult = (float) ($rate->adult_rate ?? $rate->base_rate ?? 0);
                $selectedChild = (float) ($rate->children_rate ?? $rate->adult_rate ?? $rate->base_rate ?? 0);
                $selectedInfant = (float) ($rate->infant_rate ?? 0);
              }
            }
          }
        }

        $adultUnit = max($adultUnit, $selectedAdult);
        $childUnit = max($childUnit, $selectedChild);
        $infantUnit = max($infantUnit, $selectedInfant);
      }

      if ($adultUnit <= 0 && $childUnit <= 0 && $infantUnit <= 0) {
        if ($globalMode === 'package_rate') {
          $rate = \App\Models\ActivityRate::where('activity_id', $activity->id)
            ->where('season', 'Package')
            ->orderByDesc('updated_at')
            ->first();
          if ($rate) {
            $adultUnit = (float) ($rate->adult_rate ?? $rate->base_rate ?? 0);
            $childUnit = (float) ($rate->children_rate ?? $rate->adult_rate ?? $rate->base_rate ?? 0);
            $infantUnit = (float) ($rate->infant_rate ?? 0);
          }
        }
        if ($adultUnit <= 0 && $childUnit <= 0 && $infantUnit <= 0) {
          $rate = \App\Models\ActivityRate::where('activity_id', $activity->id)
            ->where('season', '!=', 'Package')
            ->orderByDesc('created_at')
            ->first();
          if ($rate) {
            $adultUnit = (float) ($rate->adult_rate ?? $rate->base_rate ?? 0);
            $childUnit = (float) ($rate->children_rate ?? $rate->adult_rate ?? $rate->base_rate ?? 0);
            $infantUnit = (float) ($rate->infant_rate ?? 0);
          }
        }
      }

      if ($adultUnit <= 0 && $childUnit > 0 && $adultCount > 0) {
        $adultUnit = $childUnit;
      }

      $discountPct = is_numeric($globalDiscount) ? $globalDiscount : 0.0;

      if ($globalMode === 'discount_offer' && $discountPct > 0 && $discountPct <= 100) {
        $adultUnit = $adultUnit - ($adultUnit * $discountPct / 100.0);
        $childUnit = $childUnit - ($childUnit * $discountPct / 100.0);
        $infantUnit = $infantUnit - ($infantUnit * $discountPct / 100.0);
      }

      $adultTotal = $adultUnit * $adultCount;
      $childTotal = $childUnit * $childCount;
      $infantTotal = $infantUnit * $infantCount;

      return round($adultTotal + $childTotal + $infantTotal, 2);
    }

    protected function resolvePackageTransportAmount(\App\Models\Transport $transport, array $entry, int $guestCount, ?Package $package = null): float
    {
      $bestAmount = 0.0;
      $routes = $transport->routes ?? collect();
      $globalMode = $package && is_array($package->itinerary ?? null) ? ($package->itinerary['pricing_modes']['transport'] ?? 'discount_offer') : 'discount_offer';
      $globalDiscount = $package && is_array($package->itinerary ?? null) ? (float) ($package->itinerary['discounts']['transport'] ?? 5) : 5.0;

      foreach ($routes as $route) {
        $pricing = is_array($route->pricing ?? null) ? $route->pricing : (is_string($route->pricing ?? null) ? json_decode($route->pricing, true) : []);

        $candidate = 0.0;
        if ($globalMode === 'package_rate') {
          $candidate = (float) ($pricing['package_price'] ?? $pricing['package_return_price'] ?? 0);
        }

        if ($candidate <= 0) {
          $base = (float) ($pricing['price'] ?? $pricing['default_price'] ?? $pricing['single'] ?? 0);
          if ($globalMode === 'discount_offer' && $globalDiscount > 0 && $globalDiscount <= 100) {
            $base = $base - ($base * $globalDiscount / 100.0);
          }
          $candidate = $base;
        }

        if ($candidate > $bestAmount) {
          $bestAmount = $candidate;
        }
      }

      if ($bestAmount <= 0) {
        $route = $transport->routes()->first();
        if ($route) {
          $pricing = is_array($route->pricing ?? null) ? $route->pricing : (is_string($route->pricing ?? null) ? json_decode($route->pricing, true) : []);
          $bestAmount = (float) ($pricing['package_price'] ?? $pricing['price'] ?? $pricing['default_price'] ?? 0);
        }
      }

      return round($bestAmount > 0 ? $bestAmount * max(1, $guestCount) : 0.0, 2);
    }
}
