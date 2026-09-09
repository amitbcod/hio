<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    protected function activityVariantHasPackageRates($rates): bool
    {
        if (!is_array($rates) || empty($rates)) {
            return false;
        }

        $specificities = [];
        foreach ($rates as $rate) {
            if (!is_array($rate)) {
                continue;
            }

            $season = trim((string) ($rate['season'] ?? ''));
            $specificity = trim((string) ($rate['rate_specificity'] ?? ''));

            if ($season === 'Package') {
                if ($specificity !== '') {
                    $specificities[$specificity] = true;
                }
                continue;
            }

            if ($specificity !== '') {
                $specificities[$specificity] = $specificities[$specificity] ?? false;
            }
        }

        if ($specificities === []) {
            return false;
        }

        foreach ($specificities as $specificity => $hasPackage) {
            if (!$hasPackage) {
                return false;
            }
        }

        $packageSpecificities = [];
        foreach ($rates as $rate) {
            if (!is_array($rate)) {
                continue;
            }

            $season = trim((string) ($rate['season'] ?? ''));
            $specificity = trim((string) ($rate['rate_specificity'] ?? ''));

            if ($season === 'Package' && $specificity !== '') {
                $packageSpecificities[$specificity] = true;
            }
        }

        foreach ($specificities as $specificity => $hasSpecificity) {
            if ($hasSpecificity === false || empty($packageSpecificities[$specificity])) {
                return false;
            }
        }

        return true;
    }

    protected function transportRoutesHavePackagePrice($routes): bool
    {
        if (!is_array($routes) || $routes === []) {
            return false;
        }

        foreach ($routes as $route) {
            $pricing = is_array($route) ? ($route['pricing'] ?? []) : [];
            $packagePrice = $pricing['package_price'] ?? null;

            if ($packagePrice === null || $packagePrice === '' || !is_numeric($packagePrice)) {
                return false;
            }
        }

        return true;
    }

    public function index()
    {
        $groups = Group::latest()->paginate(20);
        return view('admin.groups.index', compact('groups'));
    }

    public function create()
    {
        $group = new Group();
        return view('admin.groups.create-step1', compact('group'));
    }

    public function edit(Group $group)
    {
        return view('admin.groups.create-step1', compact('group'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'group_type' => 'required|string|in:Open Group,Closed Group',
            'closed_group_client' => 'nullable|required_if:group_type,Closed Group|string|max:255',
            'no_of_days' => 'nullable|integer|min:0',
            'no_of_nights' => 'nullable|integer|min:0',
            'booking_cutoff_days' => 'nullable|integer|min:0',
            'available_from' => 'nullable|date',
            'available_to' => 'nullable|date',
            'minimum_pax' => 'nullable|integer|min:1',
            'maximum_pax' => 'nullable|integer|min:1',
        ]);

        $data['created_by'] = session('admin_id') ?? null;
        $group = Group::create($data);

        return redirect()->route('admin.groups.step2', $group->id)->with('success', 'Step 1 saved. Proceed to Step 2.');
    }

    public function update(Request $request, Group $group)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'group_type' => 'required|string|in:Open Group,Closed Group',
            'closed_group_client' => 'nullable|required_if:group_type,Closed Group|string|max:255',
            'no_of_days' => 'nullable|integer|min:0',
            'no_of_nights' => 'nullable|integer|min:0',
            'booking_cutoff_days' => 'nullable|integer|min:0',
            'available_from' => 'nullable|date',
            'available_to' => 'nullable|date',
            'minimum_pax' => 'nullable|integer|min:1',
            'maximum_pax' => 'nullable|integer|min:1',
        ]);

        $group->update($data);

        return redirect()->route('admin.groups.step2', $group->id)->with('success', 'Step 1 updated. Proceed to Step 2.');
    }

    public function step2(Group $group, Request $request)
    {
        $days = (int) ($group->no_of_days ?? 0);
        $dates = [];
        if ($days > 0 && $group->available_from) {
            $start = \Carbon\Carbon::parse($group->available_from);
            for ($i = 0; $i < $days; $i++) {
                $dates[] = $start->copy()->addDays($i)->toDateString();
            }
        } else {
            for ($i = 0; $i < max(1, $days); $i++) {
                $dates[] = null;
            }
        }

        $accQuery = \App\Models\Accommodation::query()->where('status', 'Active');
        $actQuery = \App\Models\Activity::query()->where('status', 'Active');
        $trnQuery = \App\Models\Transport::query()->where('status', 'Active');

        if ($request->filled('q_accommodation')) {
            $accQuery->where('name', 'like', '%' . $request->get('q_accommodation') . '%');
        }
        if ($request->filled('q_activity')) {
            $actQuery->where('activity_name', 'like', '%' . $request->get('q_activity') . '%');
        }
        if ($request->filled('q_transport')) {
            $trnQuery->where('name', 'like', '%' . $request->get('q_transport') . '%');
        }

        $transports = $trnQuery->get();

        $availableAccommodations = [];
        $availableActivities = [];

        foreach ($dates as $dIndex => $date) {
            $accList = \App\Models\Accommodation::query()->where('approval_status', 'Approved')->where('status', 'Active')->get();
            $accWithFlag = [];
            foreach ($accList as $acc) {
                $rooms = $acc->rooms()->get();
                $allRoomsHave = true;
                foreach ($rooms as $room) {
                    $hasPkg = \App\Models\AccommodationRate::where('accommodation_id', $acc->id)
                        ->where('room_id', $room->id)
                        ->where('rate_type', 'Package')
                        ->where('is_default', true)
                        ->exists();
                    if (!$hasPkg) { $allRoomsHave = false; break; }
                }
                $accWithFlag[] = ['model' => $acc, 'has_package' => $allRoomsHave];
            }
            $availableAccommodations[$dIndex] = collect($accWithFlag);

            $actList = \App\Models\Activity::query()->where('approval_status', 'Approved')->where('status', 'Active')->get();
            $actWithFlag = [];
            foreach ($actList as $act) {
                $variants = \App\Models\ActivityVariant::where('activity_id', $act->id)->get();
                $allVariantsHave = true;
                foreach ($variants as $variant) {
                    $variantRates = \App\Models\ActivityRate::where('activity_id', $act->id)
                        ->where('variant_id', $variant->variant_id)
                        ->get()
                        ->map(function ($rate) {
                            return [
                                'season' => (string) ($rate->season ?? ''),
                                'rate_specificity' => (string) ($rate->rate_specificity ?? ''),
                            ];
                        })
                        ->all();

                    if (!$this->activityVariantHasPackageRates($variantRates)) {
                        $allVariantsHave = false;
                        break;
                    }
                }
                $actWithFlag[] = ['model' => $act, 'has_package' => $allVariantsHave];
            }
            $availableActivities[$dIndex] = collect($actWithFlag);
        }

        $transportsRaw = $trnQuery->get();
        $transports = collect();
        foreach ($transportsRaw as $t) {
            $routes = $t->routes()->get()->map(function ($r) {
                return ['pricing' => (array) ($r->pricing ?? [])];
            })->all();
            $transports->push(['model' => $t, 'has_package' => $this->transportRoutesHavePackagePrice($routes)]);
        }

        return view('admin.groups.step2', compact('group', 'dates', 'availableAccommodations', 'availableActivities', 'transports'));
    }

    public function storeStep2(Group $group, Request $request)
    {
        $data = $request->validate([
            'itinerary' => 'required|array',
        ]);

        $existingItinerary = $group->itinerary ?? [];
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

        $group->itinerary = $mergedItinerary;
        $group->save();

        return redirect()->route('admin.groups.step3', $group->id)->with('success', 'Group itinerary saved.');
    }

    public function step3(Group $group, Request $request)
    {
        $days = (int) ($group->no_of_days ?? 0);
        $dates = [];
        if ($days > 0 && $group->available_from) {
            $start = \Carbon\Carbon::parse($group->available_from);
            for ($i = 0; $i < $days; $i++) {
                $dates[] = $start->copy()->addDays($i)->toDateString();
            }
        } else {
            for ($i = 0; $i < max(1, $days); $i++) {
                $dates[] = null;
            }
        }

        $itinerary = $group->itinerary ?? [];
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
                    $transportGroups[$serviceKey] = ['label' => $serviceLabel, 'routes' => $routes];

                    if (empty($defaultTransportService) && !empty($routes)) {
                        $defaultTransportService = $serviceKey;
                    }
                }

                $additionalRoutes = $transportRoutes->filter(fn ($route) => !isset($serviceDefinitions[$route->service_type ?? '']))->groupBy('service_type');
                foreach ($additionalRoutes as $serviceKey => $routes) {
                    $transportGroups[$serviceKey] = ['label' => ucfirst(str_replace('_', ' ', $serviceKey)), 'routes' => $routes->values()->all()];
                    if (empty($defaultTransportService) && !empty($routes)) {
                        $defaultTransportService = $serviceKey;
                    }
                }

                if (empty($defaultTransportService)) {
                    $defaultTransportService = array_key_first($transportGroups);
                }
            }

            $transportServiceGroupsByDay[$index] = ['groups' => $transportGroups, 'default' => $defaultTransportService];

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

        return view('admin.groups.step3', compact('group', 'dates', 'roomsByDay', 'mealPlans', 'propertyTypes', 'activityByDay', 'accommodationByDay', 'activityVariantPricingByDay', 'transportByDay', 'transportServiceGroupsByDay'));
    }

    public function storeStep3(Group $group, Request $request)
    {
        $data = $request->validate([
            'allocations' => 'nullable|array',
            'itinerary' => 'nullable|array',
        ]);

        $alloc = $data['allocations'] ?? [];
        $itinerary = $group->itinerary ?? [];
        $daySelections = $request->input('itinerary', []);

        foreach ($daySelections as $dayIndex => $dayData) {
            if (!isset($itinerary[$dayIndex]) || !is_array($itinerary[$dayIndex])) {
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

        foreach ($alloc as $dayIndex => $dayData) {
            if (!isset($itinerary[$dayIndex])) {
                $itinerary[$dayIndex] = [];
            }

            $rooms = $dayData['rooms'] ?? [];
            $itinerary[$dayIndex]['rooms'] = array_values(array_map('intval', $rooms));
        }

        $group->itinerary = $itinerary;
        $group->save();

        return redirect()->route('admin.groups.step4', $group->id)->with('success', 'Accommodation allocation saved.');
    }

    public function step4(Group $group)
    {
        $days = (int) ($group->no_of_days ?? 0);
        $dates = [];
        if ($days > 0 && $group->available_from) {
            $start = \Carbon\Carbon::parse($group->available_from);
            for ($i = 0; $i < $days; $i++) {
                $dates[] = $start->copy()->addDays($i)->toDateString();
            }
        } else {
            for ($i = 0; $i < max(1, $days); $i++) {
                $dates[] = null;
            }
        }

        $itinerary = $group->itinerary ?? [];
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

        $activitySelectionsByDay = [];
        $activityPricingByDay = [];
        $transportPricingByDay = [];

        foreach ($dates as $index => $date) {
            $dayIt = $itinerary[$index] ?? [];
            $selectedSelections = $dayIt['activity_selection'] ?? [];
            if (!is_array($selectedSelections)) {
                $selectedSelections = $selectedSelections ? [$selectedSelections] : [];
            }

            $activitySelectionsByDay[$index] = $selectedSelections;
            $activityPricingByDay[$index] = [];

            if (!empty($selectedSelections)) {
                foreach ($selectedSelections as $sel) {
                    if (strpos($sel, '|') === false) {
                        $activity = \App\Models\Activity::find($sel);
                        if (!$activity) continue;

                        $variants = \App\Models\ActivityVariant::where('activity_id', $activity->id)->get();
                        $variantEntries = [];
                        foreach ($variants as $variant) {
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

                            $rates = $ratesCollection->groupBy(fn ($r) => $r->season ?: 'One Season')
                                ->map(fn ($group) => $group->first())
                                ->values()->unique('rate_id')->values();

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

                    [$variantId, $pricingOption] = explode('|', $sel, 2);
                    $variantId = trim($variantId);
                    $pricingOption = trim($pricingOption);

                    $variant = \App\Models\ActivityVariant::where('variant_id', $variantId)->first();
                    if (!$variant) continue;

                    $activity = \App\Models\Activity::find($variant->activity_id);
                    if (!$activity) continue;

                    $ratesCollection = \App\Models\ActivityRate::where('activity_id', $activity->id)
                        ->where('variant_id', $variant->variant_id)
                        ->when($pricingOption, fn ($q) => $q->where('rate_specificity', $pricingOption))
                        ->where('season', '!=', 'Package')
                        ->orderBy('created_at', 'desc')
                        ->get();

                    $rates = $ratesCollection->groupBy(fn ($r) => $r->season ?: 'One Season')
                        ->map(fn ($group) => $group->first())
                        ->values()->unique('rate_id')->values();

                    $packageRatesMap = \App\Models\ActivityRate::where('activity_id', $activity->id)
                        ->where('variant_id', $variant->variant_id)
                        ->where('season', 'Package')
                        ->get()
                        ->keyBy('rate_specificity')
                        ->all();

                    $activityPricingByDay[$index][] = ['activity' => $activity, 'variants' => [['variant' => $variant, 'rates' => $rates, 'package_map' => $packageRatesMap]]];
                }
            } else {
                $activityId = $dayIt['activity'] ?? null;
                if ($activityId) {
                    $activity = \App\Models\Activity::find($activityId);
                    if ($activity) {
                        $variants = \App\Models\ActivityVariant::where('activity_id', $activity->id)->get();
                        $variantEntries = [];
                        foreach ($variants as $variant) {
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

                            $rates = $ratesCollection->groupBy(fn ($r) => $r->season ?: 'One Season')
                                ->map(fn ($group) => $group->first())
                                ->values()->unique('rate_id')->values();

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

            $selectedRouteKeys = [];
            if (!empty($dayIt['transport_schedule']) && is_array($dayIt['transport_schedule'])) {
                foreach ($dayIt['transport_schedule'] as $svcGroup) {
                    if (!is_array($svcGroup)) continue;
                    foreach ($svcGroup as $k => $v) {
                        $isSelected = false;
                        if (is_array($v)) {
                            $isSelected = !empty($v['selected']) || !empty($v['selected_route']);
                        } else {
                            $isSelected = !empty($v) || $v === '0' || $v === 0;
                        }
                        if (!$isSelected) continue;

                        if (is_string($k) && (str_ends_with($k, '-fwd') || str_ends_with($k, '-rev'))) {
                            $base = preg_replace('/-(fwd|rev)$/', '', $k);
                            $selectedRouteKeys[$base] = true;
                        } else {
                            $selectedRouteKeys[(string) $k] = true;
                        }
                    }
                }
            }

            $routes = [];
            foreach ($transport->routes as $route) {
                $pricing = is_array($route->pricing) ? $route->pricing : (is_string($route->pricing) ? json_decode($route->pricing, true) : []);
                $ridStr = (string) ($route->route_id ?? '');
                $ridNum = (string) ($route->id ?? '');

                if (empty($selectedRouteKeys)) {
                    $routes[] = ['route' => $route, 'pricing' => $pricing];
                    continue;
                }

                if (!empty($ridStr) && isset($selectedRouteKeys[$ridStr])) {
                    $routes[] = ['route' => $route, 'pricing' => $pricing];
                    continue;
                }
                if (!empty($ridNum) && isset($selectedRouteKeys[$ridNum])) {
                    $routes[] = ['route' => $route, 'pricing' => $pricing];
                    continue;
                }

                foreach (array_keys($selectedRouteKeys) as $skey) {
                    if ($skey === '') continue;
                    if (is_string($skey) && (!empty($ridStr) && strcasecmp($skey, $ridStr) === 0)) {
                        $routes[] = ['route' => $route, 'pricing' => $pricing];
                        break;
                    }
                }
            }

            $transportPricingByDay[$index] = ['transport' => $transport, 'routes' => $routes];
        }

        return view('admin.groups.step4', compact('group', 'dates', 'itinerary', 'pricingByDay', 'activitySelectionsByDay', 'activityPricingByDay', 'transportPricingByDay'));
    }

    public function storeStep4(Group $group, Request $request)
    {
        $itinerary = $group->itinerary ?? [];
        $pricingModes = $request->input('pricing_modes', []);

        $itinerary['pricing_modes'] = [
            'accommodation' => in_array($pricingModes['accommodation'] ?? 'discount_offer', ['discount_offer', 'package_rate'], true) ? $pricingModes['accommodation'] : 'discount_offer',
            'activity' => in_array($pricingModes['activity'] ?? 'discount_offer', ['discount_offer', 'package_rate'], true) ? $pricingModes['activity'] : 'discount_offer',
            'transport' => in_array($pricingModes['transport'] ?? 'discount_offer', ['discount_offer', 'package_rate'], true) ? $pricingModes['transport'] : 'discount_offer',
        ];

        $itinerary['discounts'] = [
            'accommodation' => (float) ($request->input('discounts.accommodation', 0) ?: 0),
            'activity' => (float) ($request->input('discounts.activity', 0) ?: 0),
            'transport' => (float) ($request->input('discounts.transport', 0) ?: 0),
        ];

        $group->itinerary = $itinerary;
        $group->save();

        return redirect()->route('admin.groups.step5', $group->id)->with('success', 'Group pricing saved.');
    }

    public function step5(Group $group)
    {
        $itinerary = $group->itinerary ?? [];
        $content = $itinerary['content'] ?? [];
        return view('admin.groups.step5', compact('group', 'content'));
    }

    public function storeStep5(Group $group, Request $request)
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

        $itinerary = $group->itinerary ?? [];
        $content = $itinerary['content'] ?? [];

        $fields = ['short_description','full_description','inclusions','exclusions','traveller_requirements','seo_title','seo_description','og_title','og_description','listing_category'];
        foreach ($fields as $f) {
            $content[$f] = $validated[$f] ?? null;
        }

        $tagsRaw = $validated['tags'] ?? ($content['tags'] ?? []);
        if (is_string($tagsRaw)) {
            $tags = array_values(array_filter(array_map('trim', explode(',', $tagsRaw))));
        } elseif (is_array($tagsRaw)) {
            $tags = array_values(array_filter(array_map('trim', $tagsRaw)));
        } else {
            $tags = [];
        }
        $content['tags'] = $tags;

        $existingGallery = $content['gallery'] ?? [];
        $remove = $request->input('remove_gallery', []);
        if (!is_array($remove)) $remove = [];
        $existingGallery = array_values(array_filter($existingGallery, fn($p) => !in_array($p, $remove, true)));

        $galleryFiles = $request->file('gallery', []);
        if (!is_array($galleryFiles)) $galleryFiles = [];
        $storagePath = 'groups/' . $group->id . '/gallery';
        foreach ($galleryFiles as $file) {
            if (!$file) continue;
            $path = $file->store($storagePath, 'public');
            $existingGallery[] = $path;
        }
        $content['gallery'] = array_values($existingGallery);

        if ($file = $request->file('og_image')) {
            $ogPath = $file->store('groups/' . $group->id, 'public');
            $content['og_image_path'] = $ogPath;
            $content['og_image_url'] = asset('storage/' . $ogPath);
        } else {
            if ($request->filled('og_image_url')) {
                $content['og_image_url'] = $request->input('og_image_url');
            } elseif (!empty($content['og_image_path']) && empty($content['og_image_url'])) {
                $content['og_image_url'] = asset('storage/' . $content['og_image_path']);
            }
        }

        $itinerary['content'] = $content;
        $group->itinerary = $itinerary;
        $group->save();

        return redirect()->route('admin.groups.step6', $group->id)->with('success', 'Step 5 saved.');
    }

    public function step6(Group $group)
    {
        $itinerary = $group->itinerary ?? [];
        $dayDescriptions = $itinerary['day_descriptions'] ?? [];

        $days = (int) ($group->no_of_days ?? 0);
        $days = max(1, $days);

        return view('admin.groups.step6', compact('group', 'days', 'dayDescriptions'));
    }

    public function storeStep6(Group $group, Request $request)
    {
        $data = $request->validate([
            'day_descriptions' => 'nullable|array',
            'day_descriptions.*' => 'nullable|string',
        ]);

        $itinerary = $group->itinerary ?? [];
        $descriptions = $data['day_descriptions'] ?? [];

        $normalized = array_values(array_map(function ($v) {
            return is_null($v) ? '' : trim((string) $v);
        }, $descriptions));

        $itinerary['day_descriptions'] = $normalized;
        $group->itinerary = $itinerary;
        $group->save();

        return redirect()->route('admin.groups.step7', $group->id)->with('success', 'Day-wise itinerary saved.');
    }

    public function step7(Group $group)
    {
        $itinerary = $group->itinerary ?? [];
        $days = (int) ($group->no_of_days ?? 0);
        $dates = [];
        if ($days > 0 && $group->available_from) {
            $start = \Carbon\Carbon::parse($group->available_from);
            for ($i = 0; $i < $days; $i++) {
                $dates[] = $start->copy()->addDays($i)->toDateString();
            }
        } else {
            for ($i = 0; $i < max(1, $days); $i++) {
                $dates[] = null;
            }
        }

        // Build effective group policy across selected accommodations/operators
        $effectivePolicy = $this->buildEffectiveGroupPolicy($group, $itinerary);

        // Provide option lists for editable selects in the view (same as package)
        $policyOptions = [
            'cancellation' => [
                'types' => ['Flexible', 'Moderate', 'Strict', 'Package (Default)', 'Group', 'Non-Refundable', 'No Show'],
                'beforeOptions' => ['100% Refund', '50% Refund', '20% Refund', '0% Refund'],
                'afterOptions' => ['100% Refund', '50% Refund', '20% Refund', '0% Refund'],
            ],
            'amendments' => [
                'types' => ['Flexible', 'Moderate', 'Strict'],
                'beforeOptions' => ['Available', 'Not Available'],
                'afterOptions' => ['Available', 'Not Available'],
            ],
            'postponement' => [
                'types' => ['Flexible', 'Moderate', 'Strict'],
                'beforeOptions' => ['Available', 'Not Available'],
                'afterOptions' => ['Available', 'Not Available'],
            ],
            'payment' => [
                'types' => ['100% Payment', '50% Payment', '20% Payment', '0% Payment'],
                'beforeOptions' => ['100% Payment', '50% Payment', '20% Payment', '0% Payment'],
            ],
            'refund' => [ 'types' => ['Refund Policy'] ],
            'security_deposit' => [ 'types' => ['Required'] ],
            'house_rules' => [ 'types' => ['Applicable'] ],
        ];

        $severityMaps = [
            'cancellation' => [
                'flexible' => 0,
                'moderate' => 1,
                'strict' => 2,
                'package (default)' => 3,
                'group' => 4,
                'non-refundable' => 5,
                'no show' => 6,
            ],
            'amendments' => [
                'flexible' => 0,
                'moderate' => 1,
                'strict' => 2,
            ],
            'postponement' => [
                'flexible' => 0,
                'moderate' => 1,
                'strict' => 2,
            ],
        ];

        return view('admin.groups.step7', compact('group', 'dates', 'itinerary', 'effectivePolicy', 'policyOptions', 'severityMaps'));
    }

    protected function buildEffectiveGroupPolicy(Group $group, array $itinerary = []): array
    {
        $days = max(1, (int) ($group->no_of_days ?? 0));
        $dates = [];
        if ($days > 0 && $group->available_from) {
            $start = \Carbon\Carbon::parse($group->available_from);
            for ($i = 0; $i < $days; $i++) {
                $dates[] = $start->copy()->addDays($i)->toDateString();
            }
        } else {
            for ($i = 0; $i < $days; $i++) {
                $dates[] = null;
            }
        }

        $dayPolicies = [];
        foreach ($dates as $index => $date) {
            $accommodationId = $itinerary[$index]['accommodation'] ?? null;
            if (!$accommodationId) {
                continue;
            }

            $accommodation = \App\Models\Accommodation::with('operator')->find($accommodationId);
            if (!$accommodation || !$accommodation->operator) {
                continue;
            }

            $policy = $accommodation->operator ? $accommodation->operator->effectiveGroupPolicy() : [];
            if (!empty($policy)) {
                $dayPolicies[] = $policy;
            }
        }

        // Use same base rows as package
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
                'beforeOptions' => ['100% Payment', '50% Payment', '20% Payment', '0% Refund'],
            ],
            'refund' => [ 'label' => 'Refund', 'types' => ['Refund Policy'] ],
            'security_deposit' => [ 'label' => 'Security Deposit', 'types' => ['Required'] ],
            'house_rules' => [ 'label' => 'House & Gen. Rules', 'types' => ['Applicable'] ],
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

                if (isset($entry['type']) && trim((string) $entry['type']) !== '') {
                    $typeValues[] = trim((string) $entry['type']);
                }
                if (isset($entry['before_deadline']) && trim((string) $entry['before_deadline']) !== '') {
                    $beforeValues[] = trim((string) $entry['before_deadline']);
                }
                if (isset($entry['after_deadline']) && trim((string) $entry['after_deadline']) !== '') {
                    $afterValues[] = trim((string) $entry['after_deadline']);
                }
                if (isset($entry['notes']) && trim((string) $entry['notes']) !== '') {
                    $notesValues[] = trim((string) $entry['notes']);
                }
            }

            $result[$key] = [
                'type' => $this->selectPreferredPolicyValue($key, $typeValues, $meta['types'] ?? []),
                'before_deadline' => $this->selectDeadlinePreference($beforeValues, $meta['beforeOptions'] ?? [], $key),
                'after_deadline' => $this->selectDeadlinePreference($afterValues, $meta['afterOptions'] ?? [], $key),
                'notes' => $this->selectNoteValue($notesValues),
            ];
        }

        $bookingNotes = [];
        $groupNotes = [];
        foreach ($dayPolicies as $policy) {
            if (!empty($policy['booking_notes'])) {
                $bookingNotes[] = trim((string) $policy['booking_notes']);
            }
            if (!empty($policy['group_notes'])) {
                $groupNotes[] = trim((string) $policy['group_notes']);
            }
        }

        $result['booking_notes'] = $this->selectNoteValue($bookingNotes);
        $result['group_notes'] = $this->selectNoteValue($groupNotes);

        // Apply group-level overrides stored in itinerary if present
        $overrides = $itinerary['group_policy_overrides'] ?? null;
        if (is_array($overrides)) {
            foreach ($overrides as $key => $override) {
                if ($key === 'booking_notes' || $key === 'group_notes') continue;
                if (!isset($result[$key])) continue;
                if (!is_array($override)) continue;
                if (isset($override['type']) && trim((string) $override['type']) !== '') {
                    $result[$key]['type'] = trim((string) $override['type']);
                }
                if (isset($override['before_deadline']) && trim((string) $override['before_deadline']) !== '') {
                    $result[$key]['before_deadline'] = trim((string) $override['before_deadline']);
                }
                if (isset($override['after_deadline']) && trim((string) $override['after_deadline']) !== '') {
                    $result[$key]['after_deadline'] = trim((string) $override['after_deadline']);
                }
                if (isset($override['notes']) && trim((string) $override['notes']) !== '') {
                    $result[$key]['notes'] = trim((string) $override['notes']);
                }
            }

            if (!empty($overrides['booking_notes'])) {
                $result['booking_notes'] = trim((string) $overrides['booking_notes']);
            }
            if (!empty($overrides['group_notes'])) {
                $result['group_notes'] = trim((string) $overrides['group_notes']);
            }
        }

        return $result;
    }

    protected function selectPreferredPolicyValue(string $key, array $values, array $fallbackOrder = []): string
    {
        $normalizedValues = array_values(array_filter(array_map(function ($value) {
            return trim((string) $value);
        }, $values)));

        if (empty($normalizedValues)) {
            return $fallbackOrder[0] ?? '-';
        }

        $severityMap = [
            'cancellation' => [
                'flexible' => 0,
                'moderate' => 1,
                'strict' => 2,
                'package (default)' => 3,
                'group' => 4,
                'non-refundable' => 5,
                'no show' => 6,
            ],
            'amendments' => [
                'flexible' => 0,
                'moderate' => 1,
                'strict' => 2,
            ],
            'postponement' => [
                'flexible' => 0,
                'moderate' => 1,
                'strict' => 2,
            ],
        ];

        $map = $severityMap[$key] ?? null;
        if ($map !== null) {
            $bestValue = $normalizedValues[0];
            $bestScore = -1;

            foreach ($normalizedValues as $value) {
                $normalizedKey = strtolower(trim((string) $value));
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

    protected function selectDeadlinePreference(array $values, array $fallbackOrder = [], ?string $key = null): string
    {
        $normalized = array_values(array_filter(array_map(function ($value) {
            $trimmed = trim((string) $value);
            return $trimmed !== '' ? $trimmed : null;
        }, $values)));

        if (empty($normalized)) {
            return $fallbackOrder[0] ?? '-';
        }

        if (in_array($key, ['amendments', 'postponement'], true)) {
            foreach ($normalized as $value) {
                if (strtolower(trim((string) $value)) === 'not available') {
                    return 'Not Available';
                }
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

    protected function extractPercentageValue(string $value): float
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*%/i', $value, $matches)) {
            return (float) $matches[1];
        }

        if (stripos($value, 'not available') !== false) {
            return 1000;
        }

        if (stripos($value, 'available') !== false) {
            return 0;
        }

        return 1000;
    }

    protected function selectNoteValue(array $values): string
    {
        $filtered = array_values(array_filter(array_map(function ($value) {
            $trimmed = trim((string) $value);
            return $trimmed !== '' ? $trimmed : null;
        }, $values)));

        if (empty($filtered)) {
            return '';
        }

        $unique = [];
        foreach ($filtered as $value) {
            $key = strtolower($value);
            if (!isset($unique[$key])) {
                $unique[$key] = $value;
            }
        }

        return implode(' | ', array_values($unique));
    }

    public function saveStep7(Group $group, Request $request)
    {
        $data = $request->validate([
            'action' => 'required|string|in:draft,published',
            'policies' => 'nullable|array',
            'policies.*' => 'nullable|array',
            'booking_notes' => 'nullable|string',
            'group_notes' => 'nullable|string',
        ]);

        // If admin provided policy overrides, validate severity rules against effective group defaults
        $policies = $data['policies'] ?? [];
        if (!empty($policies)) {
            $currentEffective = $this->buildEffectiveGroupPolicy($group, $group->itinerary ?? []);
            $severityMap = [
                'cancellation' => [
                    'flexible' => 0,
                    'moderate' => 1,
                    'strict' => 2,
                    'package (default)' => 3,
                    'group' => 4,
                    'non-refundable' => 5,
                    'no show' => 6,
                ],
                'amendments' => [
                    'flexible' => 0,
                    'moderate' => 1,
                    'strict' => 2,
                ],
                'postponement' => [
                    'flexible' => 0,
                    'moderate' => 1,
                    'strict' => 2,
                ],
            ];

            foreach ($severityMap as $key => $map) {
                if (!isset($policies[$key]['type'])) continue;
                $selected = strtolower(trim((string) $policies[$key]['type']));
                $selectedScore = $map[$selected] ?? null;
                $baselineType = strtolower(trim((string) ($currentEffective[$key]['type'] ?? 'group')));
                $baselineScore = $map[$baselineType] ?? ($map['group'] ?? 0);
                if ($selectedScore === null) {
                    return back()->withInput()->with('error', "Invalid {$key} policy selected.");
                }
                if ($selectedScore < $baselineScore) {
                    return back()->withInput()->with('error', ucfirst($key) . ' policy cannot be less severe than the current group default.');
                }
            }
        }

        // Persist overrides inside itinerary
        $itinerary = $group->itinerary ?? [];
        if (!empty($policies)) {
            $itinerary['group_policy_overrides'] = $policies;
        }
        if (!empty($data['booking_notes'])) {
            $itinerary['group_policy_overrides'] = $itinerary['group_policy_overrides'] ?? [];
            $itinerary['group_policy_overrides']['booking_notes'] = trim((string) $data['booking_notes']);
        }
        if (!empty($data['group_notes'])) {
            $itinerary['group_policy_overrides'] = $itinerary['group_policy_overrides'] ?? [];
            $itinerary['group_policy_overrides']['group_notes'] = trim((string) $data['group_notes']);
        }

        $group->itinerary = $itinerary;
        $group->status = $data['action'];
        $group->save();

        if ($data['action'] === 'published') {
            return redirect()->route('admin.groups.index')->with('success', 'Group status updated to Published.');
        }

        return redirect()->route('admin.groups.index')->with('success', 'Group saved successfully.');
    }
}
