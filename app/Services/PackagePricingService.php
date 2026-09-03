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
        Log::debug('PackagePricingService - itinerary', ['package_id' => $package->id, 'itinerary' => $itinerary]);
        $guestCount = max(1, $adults + $children + $infants);
        $total = 0.0;
        $items = [];

        $dayCounter = 0;
        foreach ($itinerary as $entry) {
          if (!is_array($entry)) {
            continue;
          }
          $dayCounter++;
          $dayNumber = $dayCounter;
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

            if (!empty($entry['transport']) || !empty($entry['transport_schedule'])) {
              // If itinerary entry contains explicit selected route ids (transport_schedule or route ids), price those routes individually and sum.
              // We capture metadata per selected route (route key/id and add_return flag) so we can pick package_return_price when needed.
              $selectedRoutes = [];

              // transport_schedule format (admin UI) may contain nested groups with selected flags or route_id values
              if (!empty($entry['transport_schedule']) && is_array($entry['transport_schedule'])) {
                foreach ($entry['transport_schedule'] as $svcGroup) {
                  if (!is_array($svcGroup)) continue;
                  foreach ($svcGroup as $routeKey => $routeData) {
                    if (is_array($routeData)) {
                      if (!empty($routeData['selected']) || !empty($routeData['selected_route'])) {
                        $meta = ['key' => $routeKey, 'add_return' => !empty($routeData['add_return'])];
                        if (!empty($routeData['route_id'])) {
                          $meta['route_id'] = $routeData['route_id'];
                        }
                        if (!empty($routeData['selected_route'])) {
                          $meta['selected_route'] = $routeData['selected_route'];
                        }
                        if (is_numeric($routeKey)) {
                          $meta['id'] = (int) $routeKey;
                        }
                        $selectedRoutes[] = $meta;
                      }
                    } elseif (!empty($routeData)) {
                      // could be numeric id or route_id string
                      $meta = ['key' => $routeKey, 'value' => $routeData, 'add_return' => false];
                      if (is_numeric($routeData)) $meta['id'] = (int) $routeData; else $meta['route_id'] = (string) $routeData;
                      $selectedRoutes[] = $meta;
                    } elseif (!empty($routeKey) && is_string($routeKey)) {
                      $selectedRoutes[] = ['key' => $routeKey, 'route_id' => $routeKey, 'add_return' => false];
                    }
                  }
                }
              }

              // Other possible keys (legacy): transport_routes, transport_route_ids, routes, selected_routes, selected_transport_routes, route_ids
              $possibleKeys = ['transport_routes', 'transport_route_ids', 'routes', 'selected_routes', 'selected_transport_routes', 'route_ids'];
              foreach ($possibleKeys as $k) {
                if (!empty($entry[$k]) && is_array($entry[$k])) {
                  foreach ($entry[$k] as $v) {
                    $meta = ['add_return' => false];
                    if (is_numeric($v)) {
                      $meta['id'] = (int) $v;
                    } elseif (is_array($v)) {
                      if (!empty($v['id']) && is_numeric($v['id'])) {
                        $meta['id'] = (int) $v['id'];
                      } elseif (!empty($v['route_id'])) {
                        $meta['route_id'] = $v['route_id'];
                      } elseif (!empty($v['route']) && is_string($v['route'])) {
                        $meta['route_id'] = $v['route'];
                      }
                    } elseif (!empty($v) && is_string($v)) {
                      $meta['route_id'] = $v;
                    }
                    $selectedRoutes[] = $meta;
                  }
                } elseif (!empty($entry[$k]) && is_numeric($entry[$k])) {
                  $selectedRoutes[] = ['id' => (int) $entry[$k], 'add_return' => false];
                } elseif (!empty($entry[$k]) && is_string($entry[$k])) {
                  $selectedRoutes[] = ['route_id' => $entry[$k], 'add_return' => false];
                }
              }

              // Normalize: dedupe by id/route_id/key
              $uniq = [];
              $normalized = [];
              foreach ($selectedRoutes as $sr) {
                $key = $sr['id'] ?? ($sr['route_id'] ?? ($sr['key'] ?? json_encode($sr)));
                if (!$key) continue;
                if (isset($uniq[$key])) continue;
                $uniq[$key] = true;
                $normalized[] = $sr;
              }
              $selectedRoutes = $normalized;

                Log::debug('PackagePricingService - selectedRouteIds', ['package_id' => $package->id, 'day' => $dayNumber, 'selectedRouteIds' => array_map(function($r){ return $r['id'] ?? $r['route_id'] ?? $r['key'] ?? null; }, $selectedRoutes)]);
                Log::debug('PackagePricingService - selectedRoutesMeta', ['package_id' => $package->id, 'day' => $dayNumber, 'selectedRoutes' => $selectedRoutes]);

              $dayTransportTotal = 0.0;
              $transportNames = [];

              if (!empty($selectedRoutes)) {
                foreach ($selectedRoutes as $sr) {
                  $rid = $sr['id'] ?? ($sr['route_id'] ?? ($sr['key'] ?? null));
                  $wantReturn = !empty($sr['add_return']);
                  $routeModel = null;
                  if (is_numeric($rid)) {
                    $routeModel = \App\Models\TransportRoute::find((int) $rid);
                  }
                  if (!$routeModel && is_string($rid)) {
                    // try matching by route_id string column
                    $routeModel = \App\Models\TransportRoute::where('route_id', (string) $rid)->first();
                  }
                    // Fallback: if route_id lookup failed and rid looks like a TRN-* key, try to parse tokens and match by route_from/route_to
                    if (!$routeModel && is_string($rid)) {
                      $tokens = preg_split('/[-_]/', $rid, -1, PREG_SPLIT_NO_EMPTY);
                      $from = null; $to = null;
                      if (count($tokens) >= 2) {
                        $to = array_pop($tokens);
                        $from = array_pop($tokens);
                      } elseif (count($tokens) === 1) {
                        $to = array_pop($tokens);
                      }
                      if ($from || $to) {
                        $q = \App\Models\TransportRoute::query();
                        if ($from) $q->whereRaw('LOWER(route_from) LIKE ?', ['%' . strtolower($from) . '%']);
                        if ($to) $q->whereRaw('LOWER(route_to) LIKE ?', ['%' . strtolower($to) . '%']);
                        $routeModel = $q->first();
                      }
                    }
                  if (!$routeModel) continue;
                  // Price using package_rate mode preference and whether return was requested for this selection
                  $amount = $this->resolveTransportRouteAmount($routeModel, $guestCount, $package, $wantReturn);
                  $dayTransportTotal += $amount;
                  $transportNames[] = trim(($routeModel->route_from ?? '') . ($routeModel->route_to ? ' → ' . $routeModel->route_to : ''));
                }
              } else {
                // fallback: support single transport id or array of transport ids per day
                $transportIds = is_array($entry['transport']) ? $entry['transport'] : [$entry['transport']];
                foreach ($transportIds as $tid) {
                  $t = Transport::with('routes')->find((int) $tid);
                  if (!$t) continue;

                  // Try to detect selected routes from the day entry for this transport
                  $matchedRoutes = collect();

                  // If entry contains explicit routes array
                  if (!empty($entry['routes']) && is_array($entry['routes'])) {
                    foreach ($entry['routes'] as $r) {
                      if (is_array($r) && !empty($r['id'])) {
                        $mr = \App\Models\TransportRoute::find((int) $r['id']);
                        if ($mr) $matchedRoutes->push($mr);
                      } elseif (!empty($r) && is_numeric($r)) {
                        $mr = \App\Models\TransportRoute::find((int) $r);
                        if ($mr) $matchedRoutes->push($mr);
                      } elseif (!empty($r) && is_string($r)) {
                        $mr = \App\Models\TransportRoute::where('route_id', $r)->first();
                        if ($mr) $matchedRoutes->push($mr);
                      }
                    }
                  }

                  // If still empty, try matching by route_from/route_to strings
                  if ($matchedRoutes->isEmpty()) {
                    $matchFrom = $entry['route_from'] ?? null;
                    $matchTo = $entry['route_to'] ?? null;
                    if ($matchFrom || $matchTo) {
                      $allRoutes = $t->routes ?? collect();
                      $filtered = $allRoutes->filter(function ($r) use ($matchFrom, $matchTo) {
                        $from = trim((string) ($r->route_from ?? ''));
                        $to = trim((string) ($r->route_to ?? ''));
                        if ($matchFrom && $matchTo) {
                          return strcasecmp($from, $matchFrom) === 0 && strcasecmp($to, $matchTo) === 0;
                        }
                        if ($matchFrom) return strcasecmp($from, $matchFrom) === 0;
                        return $matchTo ? strcasecmp($to, $matchTo) === 0 : false;
                      })->values();
                      if ($filtered->isNotEmpty()) $matchedRoutes = $filtered;
                    }
                  }

                  if ($matchedRoutes->isNotEmpty()) {
                    foreach ($matchedRoutes as $routeModel) {
                      $amount = $this->resolveTransportRouteAmount($routeModel, $guestCount, $package, false);
                      $dayTransportTotal += $amount;
                      $transportNames[] = trim(($routeModel->route_from ?? '') . ($routeModel->route_to ? ' → ' . $routeModel->route_to : ''));
                    }
                  } else {
                    // Last resort: price transport as before (best route per transport)
                    $amount = $this->resolvePackageTransportAmount($t, $entry, $guestCount, $package);
                    $dayTransportTotal += $amount;
                    $transportNames[] = $this->buildTransportLabel($t, $entry);
                  }
                }
              }

              if ($dayTransportTotal > 0) {
                $total += $dayTransportTotal;
                $items[] = [
                  'day' => $dayNumber,
                  'type' => 'Transport',
                  'name' => ' - ' . implode(', ', array_unique($transportNames)),
                  'amount' => round($dayTransportTotal, 2),
                ];
              }
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

        \Log::debug('PackagePricingService - transport route candidate', [
            'transport_id' => $transport->id ?? null,
            'route_id' => $route->id ?? null,
            'route_from' => $route->route_from ?? null,
            'route_to' => $route->route_to ?? null,
            'pricing_raw' => $pricing,
            'global_mode' => $globalMode,
            'global_discount' => $globalDiscount,
            'candidate' => $candidate,
            'guestCount' => $guestCount,
        ]);

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

      $result = round($bestAmount > 0 ? $bestAmount * max(1, $guestCount) : 0.0, 2);
      \Log::debug('PackagePricingService - transport resolved amount', [
          'transport_id' => $transport->id ?? null,
          'bestAmount_per_unit' => $bestAmount,
          'guestCount' => $guestCount,
          'result_total' => $result,
      ]);
      return $result;
    }

    /**
     * Resolve amount for a specific TransportRoute model (used when an itinerary selects explicit route ids).
     */
    protected function resolveTransportRouteAmount(\App\Models\TransportRoute $route, int $guestCount, ?Package $package = null, bool $wantReturn = false): float
    {
      $pricing = is_array($route->pricing ?? null) ? $route->pricing : (is_string($route->pricing ?? null) ? json_decode($route->pricing, true) : []);
      $globalMode = $package && is_array($package->itinerary ?? null) ? ($package->itinerary['pricing_modes']['transport'] ?? 'discount_offer') : 'discount_offer';
      $globalDiscount = $package && is_array($package->itinerary ?? null) ? (float) ($package->itinerary['discounts']['transport'] ?? 5) : 5.0;

      $candidate = 0.0;
      $usedPackageRate = false;
      if ($globalMode === 'package_rate') {
        if ($wantReturn) {
          $candidate = (float) ($pricing['package_return_price'] ?? $pricing['package_price'] ?? 0);
        } else {
          $candidate = (float) ($pricing['package_price'] ?? $pricing['package_return_price'] ?? 0);
        }
        if ($candidate > 0) $usedPackageRate = true;
      }

      if ($candidate <= 0) {
        $base = (float) ($pricing['price'] ?? $pricing['default_price'] ?? $pricing['single'] ?? 0);
        if ($globalMode === 'discount_offer' && $globalDiscount > 0 && $globalDiscount <= 100) {
          $base = $base - ($base * $globalDiscount / 100.0);
        }
        $candidate = $base;
      }

      \Log::debug('PackagePricingService - transport route resolved', [
        'route_id' => $route->id ?? null,
        'route_from' => $route->route_from ?? null,
        'route_to' => $route->route_to ?? null,
        'pricing' => $pricing,
        'candidate_per_unit' => $candidate,
        'guestCount' => $guestCount,
      ]);

      $perUnit = $candidate;
      // If admin selected package_rate and we used package prices, treat them as per-transfer totals (do not multiply by guest count)
      $multiplier = ($usedPackageRate ? 1 : max(1, $guestCount));
      return round($perUnit * $multiplier, 2);
    }
}
