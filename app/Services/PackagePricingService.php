<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Accommodation;
use App\Models\Activity;
use App\Models\Transport;

class PackagePricingService
{
    public function calculatePackageTotal(Package $package, int $adults = 2, int $children = 0, int $infants = 0): float
    {
        $itinerary = is_array($package->itinerary ?? null) ? $package->itinerary : [];
        $guestCount = max(1, $adults + $children + $infants);
        $total = 0.0;

        foreach ($itinerary as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $accommodation = !empty($entry['accommodation']) ? Accommodation::with('rooms')->find((int) $entry['accommodation']) : null;
            $activity = !empty($entry['activity']) ? Activity::find((int) $entry['activity']) : null;
            $transport = !empty($entry['transport']) ? Transport::with('routes')->find((int) $entry['transport']) : null;

            if ($accommodation) {
                $total += $this->resolvePackageAccommodationAmount($accommodation, $entry, $package);
            }

            if ($activity) {
                $total += $this->resolvePackageActivityAmount($activity, $entry, $guestCount, $package);
            }

            if ($transport) {
                $total += $this->resolvePackageTransportAmount($transport, $entry, $guestCount, $package);
            }
        }

        return round($total, 2);
    }

      // Public wrappers so callers can obtain per-service amounts when needed
      public function getAccommodationAmount(\App\Models\Accommodation $accommodation, array $entry, ?Package $package = null): float
      {
        return $this->resolvePackageAccommodationAmount($accommodation, $entry, $package);
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
    protected function resolvePackageAccommodationAmount(\App\Models\Accommodation $accommodation, array $entry, ?Package $package = null): float
    {
      $roomIds = array_values(array_filter(array_map('intval', (array) ($entry['rooms'] ?? []))));
      if (empty($roomIds)) {
        $roomIds = \App\Models\AccommodationRoom::where('accommodation_id', $accommodation->id)->pluck('id')->all();
      }

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

    protected function resolvePackageActivityAmount(\App\Models\Activity $activity, array $entry, int $guestCount, ?Package $package = null): float
    {
      $selection = $entry['activity_selection'] ?? [];
      if (!is_array($selection)) {
        $selection = $selection ? [$selection] : [];
      }

      $globalMode = $package && is_array($package->itinerary ?? null) ? ($package->itinerary['pricing_modes']['activity'] ?? 'discount_offer') : 'discount_offer';
      $globalDiscount = $package && is_array($package->itinerary ?? null) ? (float) ($package->itinerary['discounts']['activity'] ?? 10) : 10.0;

      $totalUnit = 0.0;
      foreach (array_values(array_filter(array_map('trim', $selection))) as $selected) {
        $perSelected = 0.0;
        if (!str_contains((string) $selected, '|')) {
          $variant = \App\Models\ActivityVariant::where('activity_id', $activity->id)->first();
          if ($variant) {
            $rates = \App\Models\ActivityRate::where('activity_id', $activity->id)
              ->where('variant_id', $variant->variant_id)
              ->orderByDesc('created_at')
              ->get();
            $rate = $rates->firstWhere('season', 'Package') ?: $rates->first();
            if ($rate) {
              $perSelected = (float) ($rate->adult_rate ?? $rate->base_rate ?? 0);
            }
          }
        } else {
          [$variantId, $rateSpecificity] = array_pad(explode('|', (string) $selected, 2), 2, null);

          if ($globalMode === 'package_rate') {
            $rate = \App\Models\ActivityRate::where('activity_id', $activity->id)
              ->where('variant_id', $variantId)
              ->where('rate_specificity', $rateSpecificity)
              ->where('season', 'Package')
              ->orderByDesc('updated_at')
              ->first();
            if ($rate) {
              $perSelected = (float) ($rate->adult_rate ?? $rate->base_rate ?? 0);
            }
          }

          if ($perSelected <= 0) {
            $ratesCollection = \App\Models\ActivityRate::where('activity_id', $activity->id)
              ->where('variant_id', $variantId)
              ->where('season', '!=', 'Package')
              ->orderByDesc('created_at')
              ->get();

            if ($ratesCollection->isEmpty()) {
              $ratesCollection = \App\Models\ActivityRate::where('activity_id', $activity->id)
                ->where('variant_id', $variantId)
                ->orderByDesc('created_at')
                ->get();
            }

            if ($ratesCollection->isNotEmpty()) {
              $grouped = $ratesCollection->groupBy(function ($r) { return $r->season ?: 'One Season'; })
                ->map(fn($g) => $g->first())->values();
              $rate = $grouped->first();
              if ($rate) {
                $perSelected = (float) ($rate->adult_rate ?? $rate->base_rate ?? 0);
              }
            }
          }
        }

        if ($perSelected > 0) {
          $totalUnit += $perSelected;
        }
      }

      if ($totalUnit <= 0) {
        if ($globalMode === 'package_rate') {
          $rate = \App\Models\ActivityRate::where('activity_id', $activity->id)
            ->where('season', 'Package')
            ->orderByDesc('updated_at')
            ->first();
          if ($rate) $totalUnit = (float) ($rate->adult_rate ?? $rate->base_rate ?? 0);
        }
        if ($totalUnit <= 0) {
          $rate = \App\Models\ActivityRate::where('activity_id', $activity->id)
            ->where('season', '!=', 'Package')
            ->orderByDesc('created_at')
            ->first();
          if ($rate) $totalUnit = (float) ($rate->adult_rate ?? $rate->base_rate ?? 0);
        }
      }

      if ($globalMode === 'discount_offer' && $totalUnit > 0) {
        $pct = $globalDiscount;
        if (is_numeric($pct) && $pct > 0 && $pct <= 100) {
          $totalUnit = $totalUnit - ($totalUnit * $pct / 100.0);
        }
      }

      return round($totalUnit > 0 ? $totalUnit * max(1, $guestCount) : 0.0, 2);
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
