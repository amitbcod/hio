<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::latest()->paginate(20);
        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        // ensure the view always has a $package variable (empty model for create)
        $package = new Package();
        return view('admin.packages.create-step1', compact('package'));
    }

    public function edit(Package $package)
    {
        // show the same step1 view but with package data
        return view('admin.packages.create-step1', compact('package'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'no_of_days' => 'nullable|integer|min:0',
            'no_of_nights' => 'nullable|integer|min:0',
            'booking_cutoff_days' => 'nullable|integer|min:0',
            'available_from' => 'nullable|date',
            'available_to' => 'nullable|date',
            'minimum_pax' => 'nullable|integer|min:1',
            'maximum_pax' => 'nullable|integer|min:1',
        ]);

        $data['created_by'] = session('admin_id') ?? null;

        $package = Package::create($data);

        return redirect()->route('admin.packages.step2', $package->id)->with('success', 'Step 1 saved. Proceed to Step 2.');
    }

    public function update(Request $request, Package $package)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'no_of_days' => 'nullable|integer|min:0',
            'no_of_nights' => 'nullable|integer|min:0',
            'booking_cutoff_days' => 'nullable|integer|min:0',
            'available_from' => 'nullable|date',
            'available_to' => 'nullable|date',
            'minimum_pax' => 'nullable|integer|min:1',
            'maximum_pax' => 'nullable|integer|min:1',
        ]);

        $package->update($data);

        return redirect()->route('admin.packages.step2', $package->id)->with('success', 'Step 1 updated. Proceed to Step 2.');
    }

    public function step2(Package $package, Request $request)
    {
        // Determine days
        $days = (int) ($package->no_of_days ?? 0);
        $dates = [];
        if ($days > 0 && $package->available_from) {
            $start = \Carbon\Carbon::parse($package->available_from);
            for ($i = 0; $i < $days; $i++) {
                $dates[] = $start->copy()->addDays($i)->toDateString();
            }
        } else {
            for ($i = 0; $i < max(1, $days); $i++) {
                $dates[] = null;
            }
        }

        // Filtered lists
        $accQuery = \App\Models\Accommodation::query()->where('status', 'Active');
        $actQuery = \App\Models\Activity::query()->where('status', 'Active');
        $trnQuery = \App\Models\Transport::query()->where('status', 'Active');

        // apply simple search filters
        if ($request->filled('q_accommodation')) {
            $accQuery->where('name', 'like', '%'.$request->get('q_accommodation').'%');
        }
        if ($request->filled('q_activity')) {
            $actQuery->where('activity_name', 'like', '%'.$request->get('q_activity').'%');
        }
        if ($request->filled('q_transport')) {
            $trnQuery->where('name', 'like', '%'.$request->get('q_transport').'%');
        }

        // Load base collections for transports (no date specific)
        $transports = $trnQuery->get();

        // For each date compute available accommodations and activities
        $availableAccommodations = [];
        $availableActivities = [];

        foreach ($dates as $dIndex => $date) {
            if ($date) {
                $accIds = \App\Models\AccommodationInventory::whereDate('date', $date)->pluck('accommodation_id')->unique()->toArray();
                if (!empty($accIds)) {
                    $availableAccommodations[$dIndex] = \App\Models\Accommodation::whereIn('id', $accIds)->where('status', 'Active')->get();
                } else {
                    // fallback: show all active accommodations when no inventory records found for the date
                    $availableAccommodations[$dIndex] = \App\Models\Accommodation::where('status', 'Active')->get();
                }

                $actIds = \App\Models\ActivityAllotment::whereDate('inventory_date', $date)
                    ->pluck('activity_id')->unique()->toArray();
                // also include calendar enabled ranges
                $calendarIds = \App\Models\ActivityAllotment::where('calendar_enabled', true)
                    ->whereDate('calendar_start', '<=', $date)
                    ->whereDate('calendar_end', '>=', $date)
                    ->pluck('activity_id')->unique()->toArray();

                $allActIds = array_unique(array_merge($actIds, $calendarIds));
                if (!empty($allActIds)) {
                    $availableActivities[$dIndex] = \App\Models\Activity::whereIn('id', $allActIds)->where('status', 'Active')->get();
                } else {
                    // fallback: show all active activities when no allotments/calendar found for the date
                    $availableActivities[$dIndex] = \App\Models\Activity::where('status', 'Active')->get();
                }
            } else {
                $availableAccommodations[$dIndex] = \App\Models\Accommodation::where('status', 'Active')->get();
                $availableActivities[$dIndex] = \App\Models\Activity::where('status', 'Active')->get();
            }
        }

        return view('admin.packages.step2', compact('package', 'dates', 'availableAccommodations', 'availableActivities', 'transports'));
    }

    public function storeStep2(Package $package, Request $request)
    {
        $data = $request->validate([
            'itinerary' => 'required|array',
        ]);

        $existingItinerary = $package->itinerary ?? [];
        $postedItinerary = $data['itinerary'];
        $mergedItinerary = $existingItinerary;

        foreach ($postedItinerary as $dayIndex => $dayData) {
            if (!isset($mergedItinerary[$dayIndex]) || !is_array($mergedItinerary[$dayIndex])) {
                $mergedItinerary[$dayIndex] = [];
            }

            foreach (['accommodation', 'activity', 'transport'] as $field) {
                if (!array_key_exists($field, $dayData)) {
                    continue;
                }

                $value = $dayData[$field];
                if ($value === '' || $value === null) {
                    unset($mergedItinerary[$dayIndex][$field]);
                } else {
                    $mergedItinerary[$dayIndex][$field] = $value;
                }
            }
        }

        // Keep any previously saved values for days not included in the current post.
        $package->itinerary = $mergedItinerary;
        $package->save();

        return redirect()->route('admin.packages.step3', $package->id)->with('success', 'Package itinerary saved.');
    }

    /**
     * Step 3 - Allocation (Accommodation rooms)
     */
    public function step3(Package $package, Request $request)
    {
        // build dates as in step2
        $days = (int) ($package->no_of_days ?? 0);
        $dates = [];
        if ($days > 0 && $package->available_from) {
            $start = \Carbon\Carbon::parse($package->available_from);
            for ($i = 0; $i < $days; $i++) {
                $dates[] = $start->copy()->addDays($i)->toDateString();
            }
        } else {
            for ($i = 0; $i < max(1, $days); $i++) {
                $dates[] = null;
            }
        }

        $itinerary = $package->itinerary ?? [];

        // For each day, load the selected activity so Step 3 can display it above allocations
        $activityByDay = [];
        $accommodationByDay = [];
        $activityVariantPricingByDay = [];
        $transportByDay = [];
        $transportServiceGroupsByDay = [];
        $serviceDefinitions = [
            'airport_transfer' => 'Airport Transfer',
            'activity_transfer' => 'Activity Transfer',
            'hotel_transfer' => 'Hotel Transfer',
            'full_day_sightseeing' => 'Full Day Sightseeing',
            'half_day_sightseeing' => 'Half Day Sightseeing',
        ];

        foreach ($dates as $index => $date) {
            $activityId = $itinerary[$index]['activity'] ?? null;
            $activityByDay[$index] = $activityId ? \App\Models\Activity::find($activityId) : null;

            $accommodationId = $itinerary[$index]['accommodation'] ?? null;
            $accommodationByDay[$index] = $accommodationId ? \App\Models\Accommodation::find($accommodationId) : null;

            $transportId = $itinerary[$index]['transport'] ?? null;
            $transportByDay[$index] = $transportId ? \App\Models\Transport::find($transportId) : null;

            $transportGroups = [];
            $defaultTransportService = null;
            if ($transportByDay[$index]) {
                $transportRoutes = $transportByDay[$index]->routes()->get();
                foreach ($serviceDefinitions as $serviceKey => $serviceLabel) {
                    $routes = $transportRoutes->where('service_type', $serviceKey)->values()->all();
                    $transportGroups[$serviceKey] = [
                        'label' => $serviceLabel,
                        'routes' => $routes,
                    ];

                    if (empty($defaultTransportService) && !empty($routes)) {
                        $defaultTransportService = $serviceKey;
                    }
                }

                $additionalRoutes = $transportRoutes->filter(fn ($route) => !isset($serviceDefinitions[$route->service_type ?? '']))->groupBy('service_type');
                foreach ($additionalRoutes as $serviceKey => $routes) {
                    $transportGroups[$serviceKey] = [
                        'label' => ucfirst(str_replace('_', ' ', $serviceKey)),
                        'routes' => $routes->values()->all(),
                    ];
                    if (empty($defaultTransportService) && !empty($routes)) {
                        $defaultTransportService = $serviceKey;
                    }
                }

                if (empty($defaultTransportService)) {
                    $defaultTransportService = array_key_first($transportGroups);
                }
            }

            $transportServiceGroupsByDay[$index] = [
                'groups' => $transportGroups,
                'default' => $defaultTransportService,
            ];

            if ($activityId) {
                $variants = \App\Models\ActivityVariant::where('activity_id', $activityId)->get();
                $options = [];

                foreach ($variants as $variant) {
                    $pricingOptions = \App\Models\ActivityRate::where('activity_id', $activityId)
                        ->where('variant_id', $variant->variant_id)
                        ->whereNotNull('rate_specificity')
                        ->pluck('rate_specificity')
                        ->unique()
                        ->filter()
                        ->values()
                        ->all();

                    if (empty($pricingOptions)) {
                        continue;
                    }

                    foreach ($pricingOptions as $pricingOption) {
                        $options[] = [
                            'variant_id' => $variant->variant_id,
                            'variant_name' => $variant->variant_name ?: 'Variant',
                            'pricing_option' => $pricingOption,
                            'label' => ($activityByDay[$index]->activity_name ?? 'Activity') . ' - ' . ($variant->variant_name ?: 'Variant') . ' - ' . $pricingOption,
                        ];
                    }
                }

                $activityVariantPricingByDay[$index] = $options;
            } else {
                $activityVariantPricingByDay[$index] = [];
            }
        }

        // For each day, load rooms for the accommodation selected in step2
        $roomsByDay = [];
        $mealPlans = [];
        $propertyTypes = [];

        foreach ($dates as $index => $date) {
            $accId = $itinerary[$index]['accommodation'] ?? null;
            if ($accId) {
                $acc = \App\Models\Accommodation::with(['rooms', 'rates'])->find($accId);
                if ($acc) {
                    $rooms = $acc->rooms()->get();
                    $roomsByDay[$index] = $rooms;

                    // collect all rate plans (rate_name) for this accommodation
                    $accRatePlans = \App\Models\AccommodationRate::where('accommodation_id', $acc->id)
                        ->whereNotNull('rate_name')
                        ->pluck('rate_name')
                        ->unique()
                        ->filter()
                        ->values()
                        ->toArray();
                    if (!empty($accRatePlans)) {
                        $mealPlans = array_values(array_unique(array_merge($mealPlans, $accRatePlans)));
                    }

                    // collect property type
                    if ($acc->property_type) {
                        $propertyTypes[] = $acc->property_type;
                    }
                } else {
                    $roomsByDay[$index] = collect();
                }
            } else {
                $roomsByDay[$index] = collect();
            }
        }

        $propertyTypes = array_values(array_unique($propertyTypes));

        return view('admin.packages.step3', compact('package', 'dates', 'roomsByDay', 'mealPlans', 'propertyTypes', 'activityByDay', 'accommodationByDay', 'activityVariantPricingByDay', 'transportByDay', 'transportServiceGroupsByDay'));
    }

    public function storeStep3(Package $package, Request $request)
    {
        $data = $request->validate([
            'allocations' => 'nullable|array',
        ]);

        $alloc = $data['allocations'] ?? [];

        $itinerary = $package->itinerary ?? [];
        $daySelections = $request->input('itinerary', []);

        foreach ($daySelections as $dayIndex => $dayData) {
            if (!isset($itinerary[$dayIndex])) {
                $itinerary[$dayIndex] = [];
            }

            if (isset($dayData['transport_schedule']) && is_array($dayData['transport_schedule'])) {
                $itinerary[$dayIndex]['transport_schedule'] = $dayData['transport_schedule'];
            } elseif (isset($itinerary[$dayIndex]['transport_schedule'])) {
                unset($itinerary[$dayIndex]['transport_schedule']);
            }

            $selectedActivityOptions = $dayData['activity_selection'] ?? [];
            if (!is_array($selectedActivityOptions)) {
                $selectedActivityOptions = [$selectedActivityOptions];
            }

            $normalized = [];
            foreach ($selectedActivityOptions as $value) {
                $value = trim((string) $value);
                if ($value === '') {
                    continue;
                }
                $normalized[] = $value;
            }

            if (!empty($normalized)) {
                $itinerary[$dayIndex]['activity_selection'] = array_values($normalized);
            } else {
                unset($itinerary[$dayIndex]['activity_selection']);
            }
        }

        // attach selected room ids to itinerary per day
        foreach ($alloc as $dayIndex => $dayData) {
            if (!isset($itinerary[$dayIndex])) {
                $itinerary[$dayIndex] = [];
            }
            $rooms = $dayData['rooms'] ?? [];
            // ensure numeric ids
            $itinerary[$dayIndex]['rooms'] = array_values(array_map('intval', $rooms));
        }

        $package->itinerary = $itinerary;
        $package->save();

        return redirect()->route('admin.packages.step4', $package->id)->with('success', 'Accommodation allocation saved.');
    }

    public function step4(Package $package)
    {
        $days = (int) ($package->no_of_days ?? 0);
        $dates = [];
        if ($days > 0 && $package->available_from) {
            $start = \Carbon\Carbon::parse($package->available_from);
            for ($i = 0; $i < $days; $i++) {
                $dates[] = $start->copy()->addDays($i)->toDateString();
            }
        } else {
            for ($i = 0; $i < max(1, $days); $i++) {
                $dates[] = null;
            }
        }

        $itinerary = $package->itinerary ?? [];
        $pricingByDay = [];

        foreach ($dates as $index => $date) {
            $accommodationId = $itinerary[$index]['accommodation'] ?? null;
            $roomIds = array_values(array_filter(array_map('intval', (array) ($itinerary[$index]['rooms'] ?? []))));

            if (!$accommodationId || empty($roomIds)) {
                $pricingByDay[$index] = [];
                continue;
            }

            $rooms = \App\Models\AccommodationRoom::where('accommodation_id', $accommodationId)
                ->whereIn('id', $roomIds)
                ->get();

            $pricingByDay[$index] = [];

            foreach ($rooms as $room) {
                $plans = $room->rates()->where('is_rate_plan', true)->whereNotNull('rate_name')->get();

                if ($plans->isEmpty()) {
                    $plans = \App\Models\AccommodationRate::where('accommodation_id', $accommodationId)
                        ->where('is_rate_plan', true)
                        ->whereNotNull('rate_name')
                        ->where(function ($query) use ($room) {
                            $query->whereNull('room_id')->orWhere('room_id', $room->id);
                        })
                        ->get();
                }

                $planPricing = [];
                foreach ($plans as $plan) {
                    $defaultPricing = \App\Models\AccommodationRate::where('accommodation_id', $accommodationId)
                        ->where('room_id', $room->id)
                        ->where('rate_name', $plan->rate_name)
                        ->where('meal_plan', $plan->meal_plan)
                        ->where('pricing_setting', $plan->pricing_setting)
                        ->where('is_rate_plan', false)
                        ->where('is_default', true)
                            ->where('rate_type', '!=', 'Package')
                        ->orderBy('valid_from')
                        ->first();

                    if (!$defaultPricing) {
                        $defaultPricing = \App\Models\AccommodationRate::where('accommodation_id', $accommodationId)
                            ->where('room_id', $room->id)
                            ->where('rate_name', $plan->rate_name)
                            ->where('meal_plan', $plan->meal_plan)
                            ->where('pricing_setting', $plan->pricing_setting)
                            ->where('is_rate_plan', false)
                            ->where('is_default', true)
                            ->orderBy('valid_from')
                            ->first();
                    }

                    $seasonalPricing = \App\Models\AccommodationRate::where('accommodation_id', $accommodationId)
                        ->where('room_id', $room->id)
                        ->where('rate_name', $plan->rate_name)
                        ->where('meal_plan', $plan->meal_plan)
                        ->where('pricing_setting', $plan->pricing_setting)
                        ->where('is_rate_plan', false)
                        ->where('is_default', false)
                        ->orderBy('valid_from')
                        ->get();

                    $packagePricing = \App\Models\AccommodationRate::where('accommodation_id', $accommodationId)
                        ->where('room_id', $room->id)
                        ->where('rate_name', $plan->rate_name)
                        ->where('meal_plan', $plan->meal_plan)
                        ->where('pricing_setting', $plan->pricing_setting)
                        ->where('rate_type', 'Package')
                        ->where('is_default', true)
                        ->first();

                    $planPricing[] = [
                        'plan' => $plan,
                        'default_pricing' => $defaultPricing,
                        'seasonal_pricing' => $seasonalPricing,
                        'package_pricing' => $packagePricing,
                    ];
                }

                if (!empty($planPricing)) {
                    $pricingByDay[$index][] = [
                        'room' => $room,
                        'plans' => $planPricing,
                    ];
                }
            }
        }

        // Build activity selections and pricing for each day (from itinerary)
        $activitySelectionsByDay = [];
        $activityPricingByDay = [];

        // Build transport pricing info for each day (read-only display of operator-set prices)
        $transportPricingByDay = [];

        foreach ($dates as $index => $date) {
            $dayIt = $itinerary[$index] ?? [];
            $selectedSelections = $dayIt['activity_selection'] ?? [];
            if (!is_array($selectedSelections)) {
                $selectedSelections = $selectedSelections ? [$selectedSelections] : [];
            }

            $activitySelectionsByDay[$index] = $selectedSelections;
            $activityPricingByDay[$index] = [];

            // If selections exist in form variant_id|pricing_option, parse them and show only matching rates
            if (!empty($selectedSelections)) {
                foreach ($selectedSelections as $sel) {
                    if (strpos($sel, '|') === false) {
                        // legacy: maybe activity id stored; try to load activity and include all variants
                        $activity = \App\Models\Activity::find($sel);
                        if (!$activity) continue;

                        $variants = \App\Models\ActivityVariant::where('activity_id', $activity->id)->get();
                        $variantEntries = [];
                        foreach ($variants as $variant) {
                            // prefer showing explicit seasonal rates (non-package) for the activity
                            $ratesCollection = \App\Models\ActivityRate::where('activity_id', $activity->id)
                                ->where('variant_id', $variant->variant_id)
                                ->where('season', '!=', 'Package')
                                ->orderBy('created_at', 'desc')
                                ->get();

                            if ($ratesCollection->isEmpty()) {
                                $ratesCollection = \App\Models\ActivityRate::where('activity_id', $activity->id)
                                    ->where('variant_id', $variant->variant_id)
                                    ->orderBy('created_at', 'desc')
                                    ->get();
                            }

                            // Group by season key and prefer the latest entry per season (operator shows latest)
                            $rates = $ratesCollection->groupBy(function ($r) {
                                return $r->season ?: 'One Season';
                            })->map(function ($group) {
                                return $group->first();
                            })->values()->unique('rate_id')->values();

                            // also collect any package-season rates for this variant keyed by specificity
                            $packageRatesMap = \App\Models\ActivityRate::where('activity_id', $activity->id)
                                ->where('variant_id', $variant->variant_id)
                                ->where('season', 'Package')
                                ->get()
                                ->keyBy('rate_specificity')
                                ->all();

                            $variantEntries[] = ['variant' => $variant, 'rates' => $rates, 'package_map' => $packageRatesMap];
                        }
                        $activityPricingByDay[$index][] = ['activity' => $activity, 'variants' => $variantEntries];
                        continue;
                    }

                    list($variantId, $pricingOption) = explode('|', $sel, 2);
                    $variantId = trim($variantId);
                    $pricingOption = trim($pricingOption);

                    $variant = \App\Models\ActivityVariant::where('variant_id', $variantId)->first();
                    if (!$variant) continue;

                    $activity = \App\Models\Activity::find($variant->activity_id);
                    if (!$activity) continue;

                    // When a specific variant|pricing option is selected, return all non-package rates
                    // for that variant+specificity (seasonal + defaults), excluding rates with rate_type = 'Package'.
                    $ratesCollection = \App\Models\ActivityRate::where('activity_id', $activity->id)
                        ->where('variant_id', $variant->variant_id)
                        ->when($pricingOption, function ($q) use ($pricingOption) {
                            return $q->where('rate_specificity', $pricingOption);
                        })
                        ->where('season', '!=', 'Package')
                        ->orderBy('created_at', 'desc')
                        ->get();

                    $rates = $ratesCollection->groupBy(function ($r) {
                        return $r->season ?: 'One Season';
                    })->map(function ($group) {
                        return $group->first();
                    })->values()->unique('rate_id')->values();

                    $packageRatesMap = \App\Models\ActivityRate::where('activity_id', $activity->id)
                        ->where('variant_id', $variant->variant_id)
                        ->where('season', 'Package')
                        ->get()
                        ->keyBy('rate_specificity')
                        ->all();

                    $activityPricingByDay[$index][] = ['activity' => $activity, 'variants' => [['variant' => $variant, 'rates' => $rates, 'package_map' => $packageRatesMap]]];
                }
            } else {
                // fallback: if no explicit selections, show activity from itinerary.activity (if present)
                $activityId = $dayIt['activity'] ?? null;
                if ($activityId) {
                    $activity = \App\Models\Activity::find($activityId);
                    if ($activity) {
                        $variants = \App\Models\ActivityVariant::where('activity_id', $activity->id)->get();
                        $variantEntries = [];
                        foreach ($variants as $variant) {
                            // prefer seasonal (non-package) rates for display
                            $ratesCollection = \App\Models\ActivityRate::where('activity_id', $activity->id)
                                ->where('variant_id', $variant->variant_id)
                                ->where('season', '!=', 'Package')
                                ->orderBy('created_at', 'desc')
                                ->get();

                            if ($ratesCollection->isEmpty()) {
                                $ratesCollection = \App\Models\ActivityRate::where('activity_id', $activity->id)
                                    ->where('variant_id', $variant->variant_id)
                                    ->orderBy('created_at', 'desc')
                                    ->get();
                            }

                            $rates = $ratesCollection->groupBy(function ($r) {
                                return $r->season ?: 'One Season';
                            })->map(function ($group) {
                                return $group->first();
                            })->values()->unique('rate_id')->values();

                            $packageRatesMap = \App\Models\ActivityRate::where('activity_id', $activity->id)
                                ->where('variant_id', $variant->variant_id)
                                ->where('season', 'Package')
                                ->get()
                                ->keyBy('rate_specificity')
                                ->all();

                            $variantEntries[] = ['variant' => $variant, 'rates' => $rates, 'package_map' => $packageRatesMap];
                        }
                        $activityPricingByDay[$index][] = ['activity' => $activity, 'variants' => $variantEntries];
                    }
                }
            }
        }

        // assemble transport pricing per day
        foreach ($dates as $index => $date) {
            $dayIt = $itinerary[$index] ?? [];
            $transportId = $dayIt['transport'] ?? null;
            if (!$transportId) {
                $transportPricingByDay[$index] = [];
                continue;
            }

            $transport = \App\Models\Transport::with('routes')->find($transportId);
            if (!$transport) {
                $transportPricingByDay[$index] = [];
                continue;
            }

            $routes = [];
            foreach ($transport->routes as $route) {
                $pricing = is_array($route->pricing) ? $route->pricing : (is_string($route->pricing) ? json_decode($route->pricing, true) : []);
                $routes[] = [
                    'route' => $route,
                    'pricing' => $pricing,
                ];
            }

            $transportPricingByDay[$index] = ['transport' => $transport, 'routes' => $routes];
        }

        return view('admin.packages.step4', compact('package', 'dates', 'itinerary', 'pricingByDay', 'activitySelectionsByDay', 'activityPricingByDay', 'transportPricingByDay'));
    }

    public function storeStep4(Package $package, Request $request)
    {
        $request->validate([
            'pricing' => 'nullable|array',
            'pricing_modes' => 'nullable|array',
            'discounts' => 'nullable|array',
        ]);

        $itinerary = $package->itinerary ?? [];

        $pricingModes = $request->input('pricing_modes', []);
        $discounts = $request->input('discounts', []);

        $itinerary['pricing_modes'] = [
            'accommodation' => in_array($pricingModes['accommodation'] ?? 'discount_offer', ['discount_offer', 'package_rate'], true) ? $pricingModes['accommodation'] : 'discount_offer',
            'activity' => in_array($pricingModes['activity'] ?? 'discount_offer', ['discount_offer', 'package_rate'], true) ? $pricingModes['activity'] : 'discount_offer',
            'transport' => in_array($pricingModes['transport'] ?? 'discount_offer', ['discount_offer', 'package_rate'], true) ? $pricingModes['transport'] : 'discount_offer',
        ];

        $itinerary['discounts'] = [
            'accommodation' => is_numeric($discounts['accommodation'] ?? null) ? max(0, min(100, (float) $discounts['accommodation'])) : 20,
            'activity' => is_numeric($discounts['activity'] ?? null) ? max(0, min(100, (float) $discounts['activity'])) : 10,
            'transport' => is_numeric($discounts['transport'] ?? null) ? max(0, min(100, (float) $discounts['transport'])) : 5,
        ];

        $pricing = $request->input('pricing', []);
        foreach ($pricing as $dayIndex => $dayData) {
            if (!isset($itinerary[$dayIndex])) {
                $itinerary[$dayIndex] = [];
            }

            $normalized = [];
            foreach ((array) $dayData as $roomId => $roomData) {
                if (!is_array($roomData)) {
                    continue;
                }

                $mode = $roomData['mode'] ?? 'discount_offer';
                if (!in_array($mode, ['discount_offer', 'package_rate'], true)) {
                    $mode = 'discount_offer';
                }

                $normalized[(int) $roomId] = [
                    'mode' => $mode,
                    'discount_percent' => isset($roomData['discount_percent']) ? trim((string) $roomData['discount_percent']) : '',
                    'selected_package' => isset($roomData['selected_package']) ? trim((string) $roomData['selected_package']) : null,
                ];
            }

            if (!empty($normalized)) {
                $itinerary[$dayIndex]['pricing'] = $normalized;
            } else {
                unset($itinerary[$dayIndex]['pricing']);
            }
        }

        $package->itinerary = $itinerary;
        $package->save();

        return redirect()->route('admin.packages.index')->with('success', 'Package pricing saved.');
    }

    /**
     * Step 5 - Content & CMS
     */
    public function step5(Package $package)
    {
        $itinerary = $package->itinerary ?? [];
        $content = $itinerary['content'] ?? [];
        return view('admin.packages.step5', compact('package', 'content'));
    }

    public function storeStep5(Package $package, Request $request)
    {
        $validated = $request->validate([
            'short_description' => 'nullable|string',
            'full_description' => 'nullable|string',
            'inclusions' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'traveller_requirements' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'listing_category' => 'nullable|string',
            'tags' => 'nullable|string',
            'gallery.*' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:5120',
            'og_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'remove_gallery' => 'nullable|array',
        ]);

        $itinerary = $package->itinerary ?? [];
        $content = $itinerary['content'] ?? [];

        // Basic text fields
        $fields = ['short_description','full_description','inclusions','exclusions','traveller_requirements','seo_title','seo_description','og_title','og_description','listing_category'];
        foreach ($fields as $f) {
            $content[$f] = $validated[$f] ?? null;
        }

        // Tags: accept comma-separated string and store as array
        $tagsRaw = $validated['tags'] ?? ($content['tags'] ?? []);
        if (is_string($tagsRaw)) {
            $tags = array_values(array_filter(array_map('trim', explode(',', $tagsRaw))));
        } elseif (is_array($tagsRaw)) {
            $tags = array_values(array_filter(array_map('trim', $tagsRaw)));
        } else {
            $tags = [];
        }
        $content['tags'] = $tags;

        // Handle existing gallery and removals
        $existingGallery = $content['gallery'] ?? [];
        $remove = $request->input('remove_gallery', []);
        if (!is_array($remove)) $remove = [];
        $existingGallery = array_values(array_filter($existingGallery, fn($p) => !in_array($p, $remove, true)));

        // Handle gallery uploads
        $galleryFiles = $request->file('gallery', []);
        if (!is_array($galleryFiles)) $galleryFiles = [];
        $storagePath = 'packages/' . $package->id . '/gallery';
        foreach ($galleryFiles as $file) {
            if (!$file) continue;
            $path = $file->store($storagePath, 'public');
            $existingGallery[] = $path;
        }
        $content['gallery'] = array_values($existingGallery);

        // Handle OG image upload or URL
        if ($file = $request->file('og_image')) {
            $ogPath = $file->store('packages/' . $package->id, 'public');
            $content['og_image_path'] = $ogPath;
            $content['og_image_url'] = asset('storage/' . $ogPath);
        } else {
            // If a URL was provided, save it. Otherwise keep existing og_image_url/path if present
            if ($request->filled('og_image_url')) {
                $content['og_image_url'] = $request->input('og_image_url');
            } elseif (!empty($content['og_image_path']) && empty($content['og_image_url'])) {
                $content['og_image_url'] = asset('storage/' . $content['og_image_path']);
            }
        }

        $itinerary['content'] = $content;
        $package->itinerary = $itinerary;
        $package->save();

        return redirect()->route('admin.packages.index')->with('success', 'Step 5 saved.');
    }

    /**
     * Step 6 - Day-wise Itinerary
     */
    public function step6(Package $package)
    {
        $itinerary = $package->itinerary ?? [];
        $dayDescriptions = $itinerary['day_descriptions'] ?? [];

        // Determine number of days from package
        $days = (int) ($package->no_of_days ?? 0);
        $days = max(1, $days);

        return view('admin.packages.step6', compact('package', 'days', 'dayDescriptions'));
    }

    public function storeStep6(Package $package, Request $request)
    {
        $data = $request->validate([
            'day_descriptions' => 'nullable|array',
            'day_descriptions.*' => 'nullable|string',
        ]);

        $itinerary = $package->itinerary ?? [];
        $descriptions = $data['day_descriptions'] ?? [];

        // normalize to sequential array of strings
        $normalized = array_values(array_map(function ($v) {
            return is_null($v) ? '' : trim((string) $v);
        }, $descriptions));

        $itinerary['day_descriptions'] = $normalized;
        $package->itinerary = $itinerary;
        $package->save();

        return redirect()->route('admin.packages.index')->with('success', 'Day-wise itinerary saved.');
    }
}
