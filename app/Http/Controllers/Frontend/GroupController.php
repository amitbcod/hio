<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Package;
use App\Models\Group;
use App\Models\Activity;
use App\Models\Transport;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function show($group)
    {
        if (!($group instanceof Group)) {
            $group = Group::find($group);
        }

        abort_if(!$group, 404);
        abort_if((string) ($group->status ?? '') !== 'published', 404);

        $itineraryData = $group->itinerary ?? [];
        $content = $itineraryData['content'] ?? [];
        $dayDescriptions = $itineraryData['day_descriptions'] ?? [];

        $gallery = collect();
        $itineraryDays = [];

        foreach ($itineraryData as $dayIndex => $dayEntry) {
            if (!is_array($dayEntry) || is_string($dayIndex)) {
                continue;
            }

            $dayImages = collect();
            $dayLabel = 'Day ' . ((int) $dayIndex + 1);

            foreach (['accommodation', 'activity', 'transport'] as $itemType) {
                $recordId = $dayEntry[$itemType] ?? null;
                if (blank($recordId)) continue;

                $model = null;
                if ($itemType === 'accommodation') $model = Accommodation::find((int) $recordId);
                if ($itemType === 'activity') $model = Activity::find((int) $recordId);
                if ($itemType === 'transport') $model = Transport::find((int) $recordId);
                if (!$model) continue;

                $modelImages = collect();
                if ($itemType === 'accommodation') {
                    $modelImages = $model->media()->pluck('path');
                } else {
                    $modelImages = collect(array_merge((array) ($model->gallery_images ?? []), [$model->hero_banner_image ?? null]));
                }

                $assetPaths = $modelImages->filter(fn($v) => is_string($v) && trim($v) !== '')->map(function($value) use ($itemType) {
                    if (is_string($value) && str_starts_with($value, 'http')) return $value;
                    return asset('storage/' . ltrim((string)$value, '/'));
                })->filter()->values();

                $dayImages = $dayImages->merge($assetPaths);
                $gallery = $gallery->merge($assetPaths);
            }

            // Build detailed service info for this day
            $accommodationDetails = null;
            $activityDetails = null;
            $transportDetails = null;

            // Accommodation details
            $accId = $dayEntry['accommodation'] ?? null;
            if (!blank($accId)) {
                $accModel = Accommodation::with(['rooms', 'media', 'operator'])->find((int) $accId);
                if ($accModel) {
                    $roomIds = array_values(array_filter(array_map('intval', (array) ($dayEntry['rooms'] ?? []))));
                    $rooms = !empty($roomIds) ? $accModel->rooms()->whereIn('id', $roomIds)->get() : $accModel->rooms()->get();
                    $accommodationDetails = [
                        'id' => $accModel->id,
                        'property_name' => $accModel->property_name ?? '',
                        'property_type' => $accModel->property_type ?? '',
                        'location' => $accModel->location ?? $accModel->region ?? '',
                        'address' => $accModel->address ?? '',
                        'star_rating' => $accModel->star_rating ?? null,
                        'operator' => $accModel->operator ? ['id' => $accModel->operator->id, 'name' => $accModel->operator->name ?? ''] : null,
                        'meal_plans' => [],
                        'rooms' => $rooms->map(function ($r) {
                            return [
                                'id' => $r->id,
                                'room_name' => $r->room_name ?? '',
                                'room_type' => $r->room_type ?? '',
                                'capacity' => (int) ($r->capacity ?? 0),
                                'children_capacity' => (int) ($r->children_capacity ?? 0),
                                'infant_capacity' => (int) ($r->infant_capacity ?? 0),
                                'max_person_capacity' => (int) ($r->max_person_capacity ?? ($r->capacity ?? 0)),
                                'allotment' => (int) ($r->allotment ?? $r->quantity ?? 1),
                                'quantity' => (int) ($r->quantity ?? $r->allotment ?? 1),
                                'max_occupancy' => $r->max_occupancy ?? null,
                                'beds' => $r->beds ?? null,
                            ];
                        })->values()->all(),
                    ];

                    // collect meal plans if rooms exist
                    try {
                        $accommodationDetails['meal_plans'] = method_exists($this, 'collectMealPlansFromRooms') ? $this->collectMealPlansFromRooms($rooms) : [];
                    } catch (\Throwable $e) {
                        $accommodationDetails['meal_plans'] = [];
                    }
                }
            }

            // Activity details
            $actId = $dayEntry['activity'] ?? null;
            if (!blank($actId)) {
                $actModel = Activity::with(['operator'])->find((int) $actId);
                if ($actModel) {
                    $activityDetails = [
                        'id' => $actModel->id,
                        'activity_name' => $actModel->activity_name ?? '',
                        'town' => $actModel->town ?? '',
                        'country' => $actModel->country ?? '',
                        'duration' => $actModel->duration ?? null,
                        'meeting_point' => $dayEntry['activity_meeting_point'] ?? $actModel->meeting_point ?? null,
                        'time' => $dayEntry['activity_time'] ?? null,
                        'notes' => $dayEntry['activity_notes'] ?? ($actModel->excerpt ?? ''),
                        'operator' => $actModel->operator ? ['id' => $actModel->operator->id, 'name' => $actModel->operator->name ?? ''] : null,
                    ];
                }
            }

            // Transport details
            $transId = $dayEntry['transport'] ?? null;
            if (!blank($transId)) {
                $transModel = Transport::with(['routes'])->find((int) $transId);
                if ($transModel) {
                    $selectedRouteIdentifiers = [];

                    if (!empty($dayEntry['transport_schedule']) && is_array($dayEntry['transport_schedule'])) {
                        foreach ($dayEntry['transport_schedule'] as $svcKey => $svcGroup) {
                            if (!is_array($svcGroup)) continue;
                            foreach ($svcGroup as $routeKey => $routeData) {
                                $isSelected = false;
                                if (is_array($routeData)) {
                                    if (!empty($routeData['selected']) || !empty($routeData['selected_route'])) {
                                        $isSelected = true;
                                    }
                                } elseif (is_string($routeData) && is_numeric($routeData)) {
                                    $isSelected = true;
                                }

                                if ($isSelected) {
                                    if (is_array($routeData) && !empty($routeData['route_id'])) {
                                        $selectedRouteIdentifiers[] = (string) $routeData['route_id'];
                                    } elseif (is_array($routeData) && !empty($routeData['selected_route'])) {
                                        $selectedRouteIdentifiers[] = (string) $routeData['selected_route'];
                                    } else {
                                        $selectedRouteIdentifiers[] = (string) $routeKey;
                                    }
                                }
                            }
                        }
                    }

                    if (empty($selectedRouteIdentifiers)) {
                        $possibleKeys = ['transport_routes', 'transport_route_ids', 'routes', 'selected_routes', 'selected_transport_routes', 'route_ids'];
                        foreach ($possibleKeys as $k) {
                            if (!empty($dayEntry[$k])) {
                                if (is_array($dayEntry[$k])) {
                                    foreach ($dayEntry[$k] as $item) {
                                        if (is_numeric($item)) $selectedRouteIdentifiers[] = (string) $item;
                                        elseif (is_array($item) && !empty($item['id'])) $selectedRouteIdentifiers[] = (string) $item['id'];
                                        elseif (is_string($item)) $selectedRouteIdentifiers[] = $item;
                                    }
                                } elseif (is_numeric($dayEntry[$k])) {
                                    $selectedRouteIdentifiers[] = (string) $dayEntry[$k];
                                } elseif (is_string($dayEntry[$k])) {
                                    $selectedRouteIdentifiers[] = $dayEntry[$k];
                                }
                            }
                        }
                    }

                    $allRoutes = $transModel->routes ?? collect();

                    if (!empty($selectedRouteIdentifiers)) {
                        $idsMap = array_values(array_unique($selectedRouteIdentifiers));
                        $routes = $allRoutes->filter(function ($r) use ($idsMap) {
                            $ridNum = (string) ($r->id ?? '');
                            $ridStr = (string) ($r->route_id ?? '');
                            return in_array($ridNum, $idsMap, true) || in_array($ridStr, $idsMap, true);
                        })->values();
                    } else {
                        $matchFrom = $dayEntry['route_from'] ?? null;
                        $matchTo = $dayEntry['route_to'] ?? null;
                        if ($matchFrom || $matchTo) {
                            $routes = $allRoutes->filter(function ($r) use ($matchFrom, $matchTo) {
                                $from = trim((string) ($r->route_from ?? ''));
                                $to = trim((string) ($r->route_to ?? ''));
                                if ($matchFrom && $matchTo) {
                                    return strcasecmp($from, $matchFrom) === 0 && strcasecmp($to, $matchTo) === 0;
                                }
                                if ($matchFrom) return strcasecmp($from, $matchFrom) === 0;
                                return $matchTo ? strcasecmp($to, $matchTo) === 0 : false;
                            })->values();
                        } else {
                            $routes = $allRoutes;
                        }
                    }

                    $transportDetails = [
                        'id' => $transModel->id,
                        'vehicle_name' => $transModel->vehicle_name ?? '',
                        'vehicle_type' => $transModel->vehicle_type ?? '',
                        'pickup_time' => $dayEntry['pickup_time'] ?? null,
                        'return_time' => $dayEntry['return_time'] ?? null,
                        'routes' => $routes->map(fn($r) => [
                            'id' => $r->id ?? null,
                            'from' => $r->route_from ?? '',
                            'to' => $r->route_to ?? '',
                            'pricing' => is_array($r->pricing ?? null) ? $r->pricing : (is_string($r->pricing ?? null) ? json_decode($r->pricing, true) : []),
                        ])->values()->all(),
                    ];
                }
            }

            $itineraryDays[] = [
                'day' => (int) $dayIndex + 1,
                'label' => $dayLabel,
                'description' => $dayDescriptions[$dayIndex] ?? '',
                'images' => $dayImages->unique()->values()->all(),
                'accommodation' => $accommodationDetails,
                'activity' => $activityDetails,
                'transport' => $transportDetails,
            ];
        }

        if ($gallery->isEmpty()) {
            $gallery = collect($content['gallery'] ?? [])->map(fn($p) => asset('storage/' . ltrim((string)$p, '/')))->filter()->values();
        }

        $pricingService = new \App\Services\PackagePricingService();
        $adults = max(1, (int) request()->query('adults', 2));
        $children = max(0, (int) request()->query('children', 0));
        $infants = max(0, (int) request()->query('infants', 0));
        $price = $pricingService->calculatePackageTotal($group, $adults, $children, $infants);

        $days = (int) ($group->no_of_days ?? max(1, count($itineraryDays)));
        $nights = (int) ($group->no_of_nights ?? max(0, $days - 1));

        $effectivePolicy = $this->buildEffectiveGroupPolicy($group, $itineraryDays);
        if (empty($effectivePolicy)) {
            $effectivePolicy = $group->effective_policy ?? [];
        }

        $groupData = [
            'id' => $group->id,
            'name' => $group->name,
            'no_of_days' => $days,
            'no_of_nights' => $nights,
            'short_description' => $content['short_description'] ?? '',
            'full_description' => $content['full_description'] ?? '',
            'gallery' => $gallery->unique()->values()->all(),
            'image' => $gallery->first() ?? asset('images/holidays-io-logo.png'),
            'itinerary_days' => $itineraryDays,
            'price' => round($price, 2),
            'location' => $content['location'] ?? '',
            'days_label' => ($days > 0 ? $days . 'D' . $nights . 'N' : ''),
            'hotel_count' => count(array_filter($itineraryDays, fn($d) => !empty($d['accommodation']))),
            'activity_count' => count(array_filter($itineraryDays, fn($d) => !empty($d['activity']))),
            'meal_count' => 0,
            'effective_policy' => $effectivePolicy,
        ];

        return view('frontend.group-show', ['group' => $groupData]);
    }

    private function buildEffectiveGroupPolicy(Group $group, array $itineraryDays = []): array
    {
        $dayPolicies = [];
        foreach ($itineraryDays as $day) {
            $accommodationId = $day['accommodation']['id'] ?? null;
            if (!$accommodationId) continue;

            $accommodation = Accommodation::with('operator')->find($accommodationId);
            if (!$accommodation || !$accommodation->operator) continue;

            $policy = $accommodation->operator ? $accommodation->operator->effectiveGroupPolicy() : [];
            if (!empty($policy)) $dayPolicies[] = $policy;
        }

        $baseRows = [
            'cancellation' => [
                'label' => 'Cancellation',
                'types' => ['Flexible', 'Moderate', 'Strict', 'Package (Default)', 'Group', 'Non-Refundable', 'No Show'],
                'beforeOptions' => ['100% Refund', '50% Refund', '20% Refund', '0% Refund'],
                'afterOptions' => ['100% Refund', '50% Refund', '20% Refund', '0% Refund'],
            ],
            'amendments' => [
                'label' => 'Amendments',
                'types' => ['Strict', 'Moderate', 'Flexible'],
                'beforeOptions' => ['Available', 'Not Available'],
                'afterOptions' => ['Available', 'Not Available'],
            ],
            'postponement' => [
                'label' => 'Postponement',
                'types' => ['Strict', 'Moderate', 'Flexible'],
                'beforeOptions' => ['Available', 'Not Available'],
                'afterOptions' => ['Available', 'Not Available'],
            ],
            'payment' => [
                'label' => 'Payment',
                'types' => ['100% Payment', '50% Payment', '20% Payment', '0% Payment'],
                'beforeOptions' => ['100% Payment', '50% Payment', '20% Payment', '0% Payment'],
            ],
            'refund' => [
                'label' => 'Refund',
                'types' => ['Refund Policy'],
            ],
            'security_deposit' => [
                'label' => 'Security Deposit',
                'types' => ['Required'],
            ],
            'house_rules' => [
                'label' => 'House & Gen. Rules',
                'types' => ['Applicable'],
            ],
        ];

        $result = [];
        foreach ($baseRows as $key => $meta) {
            $typeValues = [];
            $beforeValues = [];
            $afterValues = [];
            $notesValues = [];

            foreach ($dayPolicies as $policy) {
                $entry = $policy[$key] ?? [];
                if (!is_array($entry)) continue;

                if (isset($entry['type']) && trim((string)$entry['type']) !== '') $typeValues[] = trim((string)$entry['type']);
                if (isset($entry['before_deadline']) && trim((string)$entry['before_deadline']) !== '') $beforeValues[] = trim((string)$entry['before_deadline']);
                if (isset($entry['after_deadline']) && trim((string)$entry['after_deadline']) !== '') $afterValues[] = trim((string)$entry['after_deadline']);
                if (isset($entry['notes']) && trim((string)$entry['notes']) !== '') $notesValues[] = trim((string)$entry['notes']);
            }

            $result[$key] = [
                'type' => $this->selectPreferredPolicyValue($key, $typeValues, $meta['types'] ?? []),
                'before_deadline' => $this->selectDeadlinePreference($beforeValues, $meta['beforeOptions'] ?? [], $key),
                'after_deadline' => $this->selectDeadlinePreference($afterValues, $meta['afterOptions'] ?? [], $key),
                'notes' => $this->selectNoteValue($notesValues),
            ];
        }

        $bookingNotes = [];
        $packageNotes = [];
        foreach ($dayPolicies as $policy) {
            if (!empty($policy['booking_notes'])) $bookingNotes[] = trim((string)$policy['booking_notes']);
            if (!empty($policy['package_notes'])) $packageNotes[] = trim((string)$policy['package_notes']);
        }

        $result['booking_notes'] = $this->selectNoteValue($bookingNotes);
        $result['package_notes'] = $this->selectNoteValue($packageNotes);

        return $result;
    }

    private function selectPreferredPolicyValue(string $key, array $values, array $fallbackOrder = []): string
    {
        $normalizedValues = array_values(array_filter(array_map(function ($value) {
            return trim((string)$value);
        }, $values)));

        if (empty($normalizedValues)) return $fallbackOrder[0] ?? '-';

        $severityMap = [
            'cancellation' => [
                'flexible' => 0, 'moderate' => 1, 'strict' => 2, 'package (default)' => 3, 'group' => 4, 'non-refundable' => 5, 'no show' => 6,
            ],
            'amendments' => ['flexible' => 0, 'moderate' => 1, 'strict' => 2],
            'postponement' => ['flexible' => 0, 'moderate' => 1, 'strict' => 2],
        ];

        $map = $severityMap[$key] ?? null;
        if ($map !== null) {
            $bestValue = $normalizedValues[0];
            $bestScore = -1;
            foreach ($normalizedValues as $value) {
                $normalizedKey = strtolower(trim((string)$value));
                if (isset($map[$normalizedKey])) {
                    $score = $map[$normalizedKey];
                    if ($score > $bestScore) {
                        $bestValue = $value;
                        $bestScore = $score;
                    }
                }
            }

            return $bestValue;
        }

        return $normalizedValues[0];
    }

    private function selectDeadlinePreference(array $values, array $fallbackOrder = [], ?string $key = null): string
    {
        $normalized = array_values(array_filter(array_map(function ($value) {
            $trimmed = trim((string)$value);
            return $trimmed !== '' ? $trimmed : null;
        }, $values)));

        if (empty($normalized)) return $fallbackOrder[0] ?? '-';

        if (in_array($key, ['amendments', 'postponement'], true)) {
            foreach ($normalized as $value) {
                if (strtolower(trim((string)$value)) === 'not available') return 'Not Available';
            }
        }

        $ranked = [];
        foreach ($normalized as $value) {
            $percent = $this->extractPercentageValue($value);
            $ranked[] = ['value' => $value, 'percent' => $percent];
        }

        usort($ranked, function ($a, $b) {
            return $b['percent'] <=> $a['percent'];
        });

        return $ranked[0]['value'];
    }

    private function selectNoteValue(array $values): string
    {
        $normalized = array_values(array_filter(array_map(function ($value) {
            $trimmed = trim((string)$value);
            return $trimmed !== '' ? $trimmed : null;
        }, $values)));

        if (empty($normalized)) return '';
        return implode('; ', $normalized);
    }

    private function extractPercentageValue(string $value): int
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*%/', strtolower((string)$value), $matches)) {
            return (int) round((float) $matches[1]);
        }

        return 0;
    }

}
