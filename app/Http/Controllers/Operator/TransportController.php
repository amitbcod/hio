<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Transport;
use App\Models\TransportRate;
use App\Models\TransportBooking;
use App\Models\TransportVehicleType;
use App\Models\OperatorDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class TransportController extends Controller
{
    public function index()
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $transports = Transport::where('operator_id', $operator->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('operator.transport.index', compact('transports'));
    }

    public function create()
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $vehicleTypes = TransportVehicleType::activeList();

        return view('operator.transport.create', compact('vehicleTypes'));
    }

    protected function getOperatorTransportSettings($operator)
    {
        $settings = $operator->transport_settings ?? [];

        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?: [];
        }

        if (!is_array($settings)) {
            $settings = [];
        }

        return $settings;
    }

    protected function saveOperatorTransportSettings($operator, array $data): void
    {
        $settings = $this->getOperatorTransportSettings($operator);
        $settings = array_replace($settings, $data);
        $operator->forceFill([
            'transport_settings' => $settings,
            'transport_current_step' => $this->resolveNextTransportStep($settings),
        ])->save();
    }

    protected function resolveNextTransportStep(array $settings): int
    {
        $steps = [
            'transport_basic' => 1,
            'accounting_and_transaction' => 2,
            'policies_rules' => 3,
            'reservation_and_communication' => 4,
            'promotions_offers' => 5,
            'service_description' => 6,
        ];

        foreach ($steps as $key => $step) {
            if (empty($settings[$key])) {
                return $step;
            }
        }

        return 6;
    }

    public function showBasicDetails()
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $transportSettings = $this->getOperatorTransportSettings($operator);
        return view('operator.transport.basic-details', [
            'step' => 1,
            'title' => 'Transport Basic',
            'description' => 'Capture the core transport service details for your operator profile.',
            'transportSettings' => $transportSettings,
        ]);
    }

    public function saveBasicDetails(Request $request)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $validated = $request->validate([
            'transport_basic.service_name' => 'required|string|max:255',
            'transport_basic.transport_type' => 'required|string|in:Airport,Route,Hourly,Shared seat (optional)',
            'transport_basic.trip_type' => 'required|string|in:One-way,Round-trip',
            'transport_basic.transport_service_pattern' => 'required|string|in:ONE_WAY_AIRPORT_ARRIVAL,ONE_WAY_AIRPORT_DEPARTURE,ROUND_TRIP_AIRPORT,ACTIVITY_OUTBOUND_RETURN,FULL_DAY_SIGHTSEEING_LOOP,SHARED_SEAT_ARRIVAL,SHARED_SEAT_DEPARTURE',
            'transport_basic.service_area' => 'nullable|string|in:Mauritius – East,Mauritius – West,Mauritius – South,Mauritius – North',
            'transport_basic.status' => 'required|string|in:Draft,Submitted,Approved,Published,Suspended',
        ]);

        $this->saveOperatorTransportSettings($operator, [
            'transport_basic' => $validated['transport_basic'] ?? [],
        ]);

        return redirect()->route('operator.transport.accounting-and-transaction')
            ->with('success', 'Basic details completed successfully.');
    }

    public function showAccountingAndTransaction()
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $transportSettings = $this->getOperatorTransportSettings($operator);
        return view('operator.transport.accounting-and-transaction', [
            'step' => 2,
            'title' => 'Accounting and Transaction',
            'description' => 'Configure payment, accounting, and transaction preferences.',
            'transportSettings' => $transportSettings,
        ]);
    }

    public function saveAccountingAndTransaction(Request $request)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $validated = $request->validate([
            'accounting_and_transaction.sales_currency' => 'required|string|max:10',
            'accounting_and_transaction.payout_currency' => 'required|string|max:10',
            'accounting_and_transaction.payment_model' => 'required|string',
            'accounting_and_transaction.tax_registration_number' => 'nullable|string|max:100',
            'accounting_and_transaction.invoice_notes' => 'nullable|string',
        ]);

        $this->saveOperatorTransportSettings($operator, [
            'accounting_and_transaction' => $validated['accounting_and_transaction'] ?? [],
        ]);

        return redirect()->route('operator.transport.policies-rules')
            ->with('success', 'Accounting and transaction details saved. Continue with the next step.');
    }

    public function showPoliciesRules()
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $transportSettings = $this->getOperatorTransportSettings($operator);
        return view('operator.transport.policies-rules', [
            'step' => 3,
            'title' => 'Policies and Rules',
            'description' => 'Define the policies and operational rules for your transport services.',
            'transportSettings' => $transportSettings,
        ]);
    }

    public function savePoliciesRules(Request $request)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $transportSettings = $this->getOperatorTransportSettings($operator);
        $validated = $request->validate([
            'policies_rules.cancellation_policy_id' => 'nullable|string|max:255',
            'policies_rules.cancellation_terms' => 'required|string',
            'policies_rules.cutoff_hours' => 'required|integer|min:0',
            'policies_rules.booking_cutoff_days' => 'nullable|integer|min:0',
            'policies_rules.booking_cutoff_time' => 'nullable|date_format:H:i',
            'policies_rules.amendment_rules' => 'required|string',
        ]);

        $policiesRules = $validated['policies_rules'] ?? [];
        if (empty($policiesRules['cancellation_policy_id'])) {
            $policiesRules['cancellation_policy_id'] = data_get($transportSettings, 'policies_rules.cancellation_policy_id') ?: (string) Str::uuid();
        }

        $this->saveOperatorTransportSettings($operator, [
            'policies_rules' => $policiesRules,
        ]);

        return redirect()->route('operator.transport.reservation-and-communication')
            ->with('success', 'Policies and rules saved. Continue with the next step.');
    }

    public function showReservationAndCommunication()
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $transportSettings = $this->getOperatorTransportSettings($operator);
        return view('operator.transport.reservation-and-communication', [
            'step' => 4,
            'title' => 'Reservation and Communication',
            'description' => 'Set reservation flow and communication preferences.',
            'transportSettings' => $transportSettings,
        ]);
    }

    public function saveReservationAndCommunication(Request $request)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $validated = $request->validate([
            'reservation_and_communication.reservation_contact_name' => 'required|string|max:255',
            'reservation_and_communication.reservation_email' => 'required|email|max:255',
            'reservation_and_communication.reservation_phone' => 'nullable|string|max:50',
        ]);

        $this->saveOperatorTransportSettings($operator, [
            'reservation_and_communication' => $validated['reservation_and_communication'] ?? [],
        ]);

        return redirect()->route('operator.transport.promotions-offers')
            ->with('success', 'Reservation and communication settings saved. Continue with promotions.');
    }

    public function showPromotionsOffers()
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $transportSettings = $this->getOperatorTransportSettings($operator);
        return view('operator.transport.promotions-offers', [
            'step' => 5,
            'title' => 'Promotions & Offers',
            'description' => 'Add promotions, offers, or discount details for your transport operations.',
            'transportSettings' => $transportSettings,
        ]);
    }

    public function savePromotionsOffers(Request $request)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $validated = $request->validate([
            'promotions_offers.summary' => 'nullable|string|max:500',
            'promotions_offers.details' => 'nullable|string',
        ]);

        $this->saveOperatorTransportSettings($operator, [
            'promotions_offers' => $validated['promotions_offers'] ?? [],
        ]);

        return redirect()->route('operator.transport.service-description')
            ->with('success', 'Promotions and offers saved. Continue with service description.');
    }

    public function showServiceDescription()
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $transportSettings = $this->getOperatorTransportSettings($operator);
        return view('operator.transport.service-description', [
            'step' => 6,
            'title' => 'Service Description',
            'description' => 'Describe your transport services and what makes them special.',
            'transportSettings' => $transportSettings,
        ]);
    }

    public function saveServiceDescription(Request $request)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $validated = $request->validate([
            'service_description' => 'nullable|string|max:1000',
        ]);

        $this->saveOperatorTransportSettings($operator, [
            'service_description' => $validated['service_description'] ?? null,
        ]);

        return redirect()->route('operator.transport.index')
            ->with('success', 'Service description saved. Transport settings are complete.');
    }

    public function store(Request $request)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        $data = $request->validate([
            'vehicle_name' => 'required|string|max:150',
            'vehicle_type' => 'required|string|exists:transport_vehicle_types,name,is_active,1',
            'seating_capacity' => 'required|integer|min:1|max:100',
            'registration_number' => 'nullable|string|max:50',
            'service_description' => 'nullable|string|max:500',
        ]);

        $data['operator_id'] = $operator->id;
        $data['service_id'] = Transport::generateServiceId();
        $data['status'] = Transport::STATUS_DRAFT;
        $data['approval_status'] = 'Draft';
        $data['step1_basics'] = 1;

        $transport = Transport::create($data);

        return redirect()->route('operator.transport.step2.show', $transport->id)
            ->with('success', 'Transport created. Continue with the transport setup steps.');
    }

    public function show(Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        $rates = $transport->rates()->get();

        return view('operator.transport.show', compact('transport', 'rates'));
    }

    public function edit(Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        return view('operator.transport.edit', compact('transport'));
    }

    public function update(Transport $transport, Request $request)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        $data = $request->validate([
            'vehicle_name' => 'required|string|max:150',
            'vehicle_type' => 'required|string|exists:transport_vehicle_types,name,is_active,1',
            'seating_capacity' => 'required|integer|min:1|max:100',
            'registration_number' => 'nullable|string|max:50',
            'service_description' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:25',
            'contact_email' => 'nullable|email|max:100',
            'overview' => 'nullable|string',
            'amenities' => 'nullable|array',
        ]);

        $transport->update($data);

        return redirect()->route('operator.transport.show', $transport->id)
            ->with('success', 'Transport updated.');
    }

    // ════════════════════════════════════════════════════════════════════════
    // Step 2: Routes & Pricing
    // ════════════════════════════════════════════════════════════════════════

    private function getTransportPricingServiceDefinitions(array $regions): array
    {
        $regionOptions = array_values(array_unique(array_filter(array_map('trim', $regions))));
        $regionOptions = array_values(array_unique(array_merge(['Airport'], $regionOptions)));
        $baseRegions = array_values(array_values(array_filter($regionOptions, fn ($region) => $region !== 'Airport')));

        $airportPairs = array_map(fn ($region) => ['route_from' => 'Airport', 'route_to' => $region], $baseRegions);
        $interRegionPairs = [];
        foreach ($baseRegions as $fromRegion) {
            foreach ($baseRegions as $toRegion) {
                if ($fromRegion !== $toRegion) {
                    $interRegionPairs[] = ['route_from' => $fromRegion, 'route_to' => $toRegion];
                }
            }
        }

        $configuredPairs = \App\Models\TransportServiceRoutePair::query()
            ->where('is_active', true)
            ->get()
            ->groupBy('service_type');

        return [
            'airport_transfer' => [
                'label' => 'Airport Transfer',
                'pairs' => $configuredPairs->has('airport_transfer')
                    ? $configuredPairs['airport_transfer']->map(fn ($pair) => ['route_from' => $pair->route_from, 'route_to' => $pair->route_to])->values()->all()
                    : $airportPairs,
            ],
            'activity_transfer' => [
                'label' => 'Activity Transfer',
                'pairs' => $configuredPairs->has('activity_transfer')
                    ? $configuredPairs['activity_transfer']->map(fn ($pair) => ['route_from' => $pair->route_from, 'route_to' => $pair->route_to])->values()->all()
                    : array_values(array_merge($airportPairs, $interRegionPairs)),
            ],
            'full_day_sightseeing' => [
                'label' => 'Full Day Sightseeing',
                'pairs' => $configuredPairs->has('full_day_sightseeing')
                    ? $configuredPairs['full_day_sightseeing']->map(fn ($pair) => ['route_from' => $pair->route_from, 'route_to' => $pair->route_to])->values()->all()
                    : array_values(array_merge($airportPairs, $interRegionPairs)),
            ],
        ];
    }

    private function buildServiceRouteEntries(array $serviceDefinitions, array $savedRoutes, $existingRoutes, string $serviceKey): array
    {
        $serviceDefinition = $serviceDefinitions[$serviceKey] ?? ['label' => ucfirst(str_replace('_', ' ', $serviceKey)), 'pairs' => []];
        $routeEntries = [];
        $existingRouteCollection = collect($existingRoutes);

        foreach ($serviceDefinition['pairs'] as $pair) {
            $pairKey = $pair['route_from'] . '-' . $pair['route_to'];
            $existingRoute = $existingRouteCollection->first(function ($route) use ($pairKey, $serviceKey) {
                $routeFrom = $route->route_from ?? $route->pickup_value;
                $routeTo = $route->route_to ?? $route->dropoff_value;
                $routeService = $route->service_type ?? 'airport_transfer';
                return $routeService === $serviceKey && ($routeFrom . '-' . $routeTo) === $pairKey;
            });

            if (!empty($savedRoutes)) {
                $savedRoute = collect($savedRoutes)->first(function ($route) use ($serviceKey, $pair) {
                    $routeService = $route['service_type'] ?? 'airport_transfer';
                    return $routeService === $serviceKey && (($route['route_from'] ?? '') === $pair['route_from']) && (($route['route_to'] ?? '') === $pair['route_to']);
                });
                if ($savedRoute) {
                    $routeEntries[] = $savedRoute;
                    continue;
                }
            }

            $routeEntries[] = [
                'route_id' => $existingRoute->route_id ?? '',
                'service_type' => $serviceKey,
                'route_from' => $pair['route_from'],
                'route_to' => $pair['route_to'],
                'route_type' => $existingRoute->route_type ?? 'Route',
                'pickup_type' => $existingRoute->pickup_type ?? 'Location zone',
                'pickup_value' => $existingRoute->pickup_value ?? $pair['route_from'],
                'dropoff_type' => $existingRoute->dropoff_type ?? 'Location zone',
                'dropoff_value' => $existingRoute->dropoff_value ?? $pair['route_to'],
                'duration_estimate' => $existingRoute->duration_estimate ?? null,
                'pricing' => $existingRoute->pricing ?? [],
            ];
        }

        return $routeEntries;
    }

    public function step2RoutesPricing(Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        $routes = $transport->routes()->get();
        $vehicleTypes = TransportVehicleType::activeList();
        $regionOptions = Region::orderBy('name')->pluck('name')->toArray();
        $serviceDefinitions = $this->getTransportPricingServiceDefinitions($regionOptions);

        $serviceGroups = [];
        $savedRoutes = old('routes', []);
        foreach ($serviceDefinitions as $serviceKey => $serviceDefinition) {
            $serviceGroups[$serviceKey] = [
                'label' => $serviceDefinition['label'],
                'routes' => $this->buildServiceRouteEntries($serviceDefinitions, $savedRoutes, $routes, $serviceKey),
            ];
        }

        return view('operator.transport.step2-routes-pricing', compact('transport', 'routes', 'vehicleTypes', 'serviceGroups', 'regionOptions'));
    }

    public function saveStep2RoutesPricing(Request $request, Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        $regionOptions = Region::orderBy('name')->pluck('name')->toArray();
        $regionOptions = array_values(array_unique(array_merge(['Airport'], array_filter(array_map('trim', $regionOptions)))));
        $regionRule = 'required|string|in:' . implode(',', $regionOptions);
        $serviceTypeRule = 'required|string|in:airport_transfer,activity_transfer,full_day_sightseeing';
        $saveService = $request->input('save_service');
        $routesPayload = $request->input('routes', []);

        if ($saveService) {
            $routesPayload = array_values(array_filter($routesPayload, fn ($route) => ($route['service_type'] ?? null) === $saveService));
        }

        $validator = Validator::make(['routes' => $routesPayload], [
            'routes' => 'required|array|min:1',
            'routes.*.route_id' => 'nullable|string|max:100',
            'routes.*.service_type' => $serviceTypeRule,
            'routes.*.route_from' => $regionRule,
            'routes.*.route_to' => $regionRule,
            'routes.*.route_type' => 'nullable|in:Airport,Route,Hourly',
            'routes.*.pickup_type' => 'nullable|in:Airport,Address,Hotel,Location zone',
            'routes.*.pickup_value' => 'nullable|string|max:255',
            'routes.*.dropoff_type' => 'nullable|in:Airport,Address,Hotel,Location zone',
            'routes.*.dropoff_value' => 'nullable|string|max:255',
            'routes.*.duration_estimate' => 'nullable|integer|min:0',
            'routes.*.pricing' => 'required|array',
            'routes.*.pricing.vehicle_type' => 'required|string|exists:transport_vehicle_types,name,is_active,1',
            'routes.*.pricing.default_price' => 'nullable|numeric|min:0',
            'routes.*.pricing.return_price' => 'nullable|numeric|min:0',
            'routes.*.pricing.seasonal' => 'nullable|array',
            'routes.*.pricing.seasonal.*.start' => 'nullable|date',
            'routes.*.pricing.seasonal.*.end' => 'nullable|date|after_or_equal:routes.*.pricing.seasonal.*.start',
            'routes.*.pricing.seasonal.*.price' => 'nullable|numeric|min:0',
            'routes.*.pricing.seasonal.*.return_price' => 'nullable|numeric|min:0',
        ]);

        $validator->after(function ($validator) use ($request, $routesPayload) {
            $routes = $routesPayload;
            $activeRouteCount = 0;

            foreach ($routes as $index => $routeData) {
                $pricing = $routeData['pricing'] ?? [];
                $defaultPrice = $pricing['default_price'] ?? null;
                $seasonals = $pricing['seasonal'] ?? [];
                $returnPrice = $pricing['return_price'] ?? null;

                $hasDefaultPrice = array_key_exists('default_price', $pricing) && $defaultPrice !== null && $defaultPrice !== '';
                $hasReturnPrice = array_key_exists('return_price', $pricing) && $returnPrice !== null && $returnPrice !== '';
                $hasSeasonalValues = false;

                foreach ($seasonals as $seasonal) {
                    if (!is_array($seasonal)) {
                        continue;
                    }
                    foreach ($seasonal as $value) {
                        if ($value !== null && $value !== '') {
                            $hasSeasonalValues = true;
                            break 2;
                        }
                    }
                }

                if (!$hasDefaultPrice && !$hasReturnPrice && !$hasSeasonalValues) {
                    continue;
                }

                $activeRouteCount++;

                if (!$hasSeasonalValues && !$hasDefaultPrice) {
                    $validator->errors()->add("routes.{$index}.pricing.default_price", 'Default price is required when no seasonal pricing is provided.');
                }

                $ranges = [];
                foreach ($seasonals as $sIndex => $seasonal) {
                    if (!is_array($seasonal)) {
                        continue;
                    }

                    $startValue = trim((string) ($seasonal['start'] ?? ''));
                    $endValue = trim((string) ($seasonal['end'] ?? ''));
                    $priceValue = $seasonal['price'] ?? null;
                    $returnPriceValue = $seasonal['return_price'] ?? null;
                    $hasSeasonRowValues = $startValue !== '' || $endValue !== '' || $priceValue !== null && $priceValue !== '' || $returnPriceValue !== null && $returnPriceValue !== '';

                    if (!$hasSeasonRowValues) {
                        continue;
                    }

                    if ($startValue === '' || $endValue === '' || $priceValue === null || $priceValue === '') {
                        $validator->errors()->add("routes.{$index}.pricing.seasonal.{$sIndex}.start", 'Seasonal pricing rows require start, end, and price values.');
                        $validator->errors()->add("routes.{$index}.pricing.seasonal.{$sIndex}.end", 'Seasonal pricing rows require start, end, and price values.');
                        continue;
                    }

                    $start = strtotime($startValue);
                    $end = strtotime($endValue);

                    if ($start === false || $end === false) {
                        $validator->errors()->add("routes.{$index}.pricing.seasonal.{$sIndex}.start", 'Seasonal dates must be valid.');
                        $validator->errors()->add("routes.{$index}.pricing.seasonal.{$sIndex}.end", 'Seasonal dates must be valid.');
                        continue;
                    }

                    if ($start > $end) {
                        $validator->errors()->add("routes.{$index}.pricing.seasonal.{$sIndex}.end", 'Seasonal end date must be after or equal to start date.');
                        continue;
                    }

                    foreach ($ranges as $range) {
                        if (!($end < $range['start'] || $start > $range['end'])) {
                            $validator->errors()->add("routes.{$index}.pricing.seasonal.{$sIndex}.start", 'Seasonal date ranges must not overlap.');
                            $validator->errors()->add("routes.{$index}.pricing.seasonal.{$sIndex}.end", 'Seasonal date ranges must not overlap.');
                        }
                    }

                    $ranges[] = ['start' => $start, 'end' => $end];
                }
            }

            if ($activeRouteCount === 0) {
                $validator->errors()->add('routes', 'Please provide at least one route pricing entry.');
            }
        });

        $validator->validate();

        $data = $validator->validated();

        if ($saveService) {
            $transport->routes()->where('service_type', $saveService)->delete();
        } else {
            $transport->routes()->delete();
        }

        foreach ($data['routes'] as $index => $routeData) {
            $routePricing = $routeData['pricing'] ?? [];
            $defaultPrice = $routePricing['default_price'] ?? null;
            $returnPrice = $routePricing['return_price'] ?? null;
            $seasonals = $routePricing['seasonal'] ?? [];

            $hasDefaultPrice = array_key_exists('default_price', $routePricing) && $defaultPrice !== null && $defaultPrice !== '';
            $hasReturnPrice = array_key_exists('return_price', $routePricing) && $returnPrice !== null && $returnPrice !== '';
            $hasSeasonalValues = false;
            foreach ($seasonals as $seasonal) {
                if (!is_array($seasonal)) {
                    continue;
                }
                foreach ($seasonal as $value) {
                    if ($value !== null && $value !== '') {
                        $hasSeasonalValues = true;
                        break 2;
                    }
                }
            }

            if (!$hasDefaultPrice && !$hasReturnPrice && !$hasSeasonalValues) {
                continue;
            }

            $pricing = [
                'vehicle_type' => $routePricing['vehicle_type'] ?? $transport->vehicle_type,
                'default_price' => $defaultPrice !== '' ? $defaultPrice : null,
                'return_price' => $returnPrice !== '' ? $returnPrice : null,
                'seasonal' => array_values($seasonals),
            ];

            $routeId = trim((string) ($routeData['route_id'] ?? ''));
            if ($routeId === '') {
                $routeId = 'TRN-' . $transport->id . '-' . ($index + 1);
            }

            $routeFrom = $routeData['route_from'] ?? ($routeData['pickup_value'] ?? null);
            $routeTo = $routeData['route_to'] ?? ($routeData['dropoff_value'] ?? null);
            $routeType = $routeData['route_type'] ?? (($routeFrom === 'Airport' || $routeTo === 'Airport') ? 'Airport' : 'Route');
            $pickupType = $routeData['pickup_type'] ?? 'Location zone';
            $dropoffType = $routeData['dropoff_type'] ?? 'Location zone';
            $pickupValue = $routeData['pickup_value'] ?? $routeFrom;
            $dropoffValue = $routeData['dropoff_value'] ?? $routeTo;
            $serviceType = $routeData['service_type'] ?? 'airport_transfer';

            $transport->routes()->create([
                'route_id' => $routeId,
                'service_type' => $serviceType,
                'route_type' => $routeType,
                'pickup_type' => $pickupType,
                'pickup_value' => $pickupValue,
                'dropoff_type' => $dropoffType,
                'dropoff_value' => $dropoffValue,
                'route_from' => $routeFrom,
                'route_to' => $routeTo,
                'duration_estimate' => $routeData['duration_estimate'] ?? null,
                'pricing' => $pricing,
            ]);
        }

        $transport->update(['step2_routes_pricing' => 1]);

        if ($saveService) {
            $serviceLabels = [
                'airport_transfer' => 'Airport Transfer',
                'activity_transfer' => 'Activity Transfer',
                'full_day_sightseeing' => 'Full Day Sightseeing',
            ];

            return redirect()->route('operator.transport.step2.show', $transport->id)
                ->with('success', ($serviceLabels[$saveService] ?? ucfirst(str_replace('_', ' ', $saveService))) . ' pricing saved.');
        }

        return redirect()->route('operator.transport.step2.car_rental.show', $transport->id)->with('success', 'Routes and pricing saved. You can now add car rental prices.');

        return redirect()->route('operator.transport.step3.show', $transport->id)
            ->with('success', 'Routes and pricing saved.');
    }

    // ════════════════════════════════════════════════════════════════════════
    // Step 3: Media
    // ════════════════════════════════════════════════════════════════════════

    public function step3Media(Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        return view('operator.transport.step3-media', compact('transport'));
    }

    public function saveStep3Media(Request $request, Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        $validated = $request->validate([
            'media_files.*' => 'nullable|image|max:10240',
        ]);

        try {
            if ($request->hasFile('media_files')) {
                $paths = [];
                foreach ($request->file('media_files') as $file) {
                    $paths[] = $file->store('transports/media', 'public');
                }

                $existing = $transport->gallery_images ?? [];
                $transport->gallery_images = array_merge($existing, $paths);
            }

            $transport->step3_media = 1;
            $transport->save();

            return redirect()->route('operator.transport.step4.show', $transport->id)
                ->with('success', 'Media saved.');
        } catch (\Exception $e) {
            \Log::error('saveStep3Media error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('operator.transport.step3.show', $transport->id)
                ->with('error', 'Failed to upload media: ' . $e->getMessage());
        }
    }

    public function deleteStep3MediaImage(Request $request, Transport $transport, int $imageIndex)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        $galleryImages = $transport->gallery_images ?? [];
        if (!is_array($galleryImages) || !array_key_exists($imageIndex, $galleryImages)) {
            return redirect()->route('operator.transport.step3.show', $transport->id)
                ->with('error', 'Image not found.');
        }

        $imagePath = $galleryImages[$imageIndex];
        unset($galleryImages[$imageIndex]);
        $transport->gallery_images = array_values($galleryImages);

        if (is_string($imagePath) && !blank($imagePath)) {
            $publicPath = preg_replace('#^(storage/|public/)#', '', ltrim($imagePath, '/'));
            if (Storage::disk('public')->exists($publicPath)) {
                Storage::disk('public')->delete($publicPath);
            }
        }

        $transport->save();

        return redirect()->route('operator.transport.step3.show', $transport->id)
            ->with('success', 'Image removed successfully.');
    }

    // ════════════════════════════════════════════════════════════════════════
    // Step 4: Compliance
    // ════════════════════════════════════════════════════════════════════════

    public function step4Compliance(Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        return view('operator.transport.step4-compliance', compact('transport'));
    }

    public function saveStep4Compliance(Request $request, Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        $data = $request->validate([
            'insurance_provider' => 'nullable|string|max:150',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_expiration' => 'nullable|date',
            'license_number' => 'nullable|string|max:100',
            'license_expiration' => 'nullable|date',
            'terms_conditions' => 'nullable|string',
            'cancellation_policy' => 'nullable|string',
        ]);

        $data['step4_compliance'] = 1;
        $transport->update($data);

        return redirect()->route('operator.transport.step5.show', $transport->id)
            ->with('success', 'Compliance information saved.');
    }

    // ════════════════════════════════════════════════════════════════════════
    // Step 5: Promotions & Offers
    // ════════════════════════════════════════════════════════════════════════

    public function step5PromotionsOffers(Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        return view('operator.transport.step5-promotions-offers', compact('transport'));
    }

    public function saveStep5PromotionsOffers(Request $request, Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        $validated = $request->validate([
            'promotions_offers.promo_type' => 'nullable|string|max:50',
            'promotions_offers.promo_value' => 'nullable|numeric|min:0',
            'promotions_offers.valid_from' => 'nullable|string|max:50',
            'promotions_offers.valid_to' => 'nullable|string|max:50',
        ]);

        $promoData = $validated['promotions_offers'] ?? [];
        if (empty($promoData['promo_id'])) {
            $promoData['promo_id'] = 'PROMO-TR-' . $transport->id . '-' . strtoupper(Str::random(4));
        }

        $transport->update([
            'promotions_offers' => $promoData,
            'step5_promotions_offers' => 1,
        ]);

        return redirect()->route('operator.transport.step6-service-description.show', $transport->id)
            ->with('success', 'Promotions and offers saved.');
    }

    // ════════════════════════════════════════════════════════════════════════
    // Step 6: Service Description
    // ════════════════════════════════════════════════════════════════════════

    public function step6ServiceDescription(Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        return view('operator.transport.step6-service-description', compact('transport'));
    }

    public function step2CarRental(Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        return view('operator.transport.step2-car-rental', compact('transport'));
    }

    public function saveStep2CarRental(Request $request, Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        $validated = $request->validate([
            'car_rental_prices.per_hour' => 'nullable|numeric|min:0',
            'car_rental_prices.per_4h' => 'nullable|numeric|min:0',
            'car_rental_prices.per_8h' => 'nullable|numeric|min:0',
            'car_rental_prices.per_12h' => 'nullable|numeric|min:0',
            'car_rental_prices.per_24h' => 'nullable|numeric|min:0',
            'car_rental_prices.seasonal' => 'nullable|array',
            'car_rental_prices.seasonal.*.start' => 'required_with:car_rental_prices.seasonal|date',
            'car_rental_prices.seasonal.*.end' => 'required_with:car_rental_prices.seasonal|date|after_or_equal:car_rental_prices.seasonal.*.start',
            'car_rental_prices.seasonal.*.per_hour' => 'nullable|numeric|min:0',
            'car_rental_prices.seasonal.*.per_4h' => 'nullable|numeric|min:0',
            'car_rental_prices.seasonal.*.per_8h' => 'nullable|numeric|min:0',
            'car_rental_prices.seasonal.*.per_12h' => 'nullable|numeric|min:0',
            'car_rental_prices.seasonal.*.per_24h' => 'nullable|numeric|min:0',
        ]);

        $prices = $validated['car_rental_prices'] ?? [];
        $prices['seasonal'] = array_values($prices['seasonal'] ?? []);
        $transport->update([
            'car_rental_prices' => $prices,
            'step2_car_rental' => 1,
        ]);

        return redirect()->route('operator.transport.step3.show', $transport->id)->with('success', 'Car rental prices saved. Continue to media step.');
    }

    public function saveStep6ServiceDescription(Request $request, Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        $validated = $request->validate([
            'long_description' => 'nullable|string',
            'long_description_fr' => 'nullable|string',
            'inclusions' => 'nullable|string',
            'inclusions_fr' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'exclusions_fr' => 'nullable|string',
            'pickup_instructions' => 'nullable|string',
            'pickup_instructions_fr' => 'nullable|string',
        ]);

        $transport->update(array_merge($validated, [
            'step6_service_description' => 1,
        ]));

        return redirect()->route('operator.transport.step6.show', $transport->id)
            ->with('success', 'Service description details saved.');
    }

    // ════════════════════════════════════════════════════════════════════════
    // Step 7: SEO & Social
    // ════════════════════════════════════════════════════════════════════════

    public function step6Seo(Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        return view('operator.transport.step6-seo', compact('transport'));
    }

    public function saveStep6Seo(Request $request, Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        $data = $request->validate([
            'seo_title' => 'nullable|string|max:200',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:500',
            'short_description' => 'nullable|string|max:300',
            'short_description_fr' => 'nullable|string|max:300',
        ]);

        $data['step6_seo_social'] = 1;
        $transport->update($data);

        return redirect()->route('operator.transport.step7.show', $transport->id)
            ->with('success', 'SEO information saved.');
    }

    // ════════════════════════════════════════════════════════════════════════
    // Step 7: Publish & Submit for Approval
    // ════════════════════════════════════════════════════════════════════════

    public function step7Publish(Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        return view('operator.transport.step7-publish', compact('transport'));
    }

    public function submitForApproval(Request $request, Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        // Check all essential steps are complete
        $essentialSteps = ['step1_basics', 'step2_routes_pricing', 'step3_media', 'step4_compliance', 'step5_promotions_offers', 'step6_service_description', 'step6_seo_social'];
        foreach ($essentialSteps as $step) {
            if (!$transport->{$step}) {
                return back()->with('error', 'Please complete all essential setup steps before submitting for approval.');
            }
        }

        $transport->update([
            'approval_status' => 'Pending',
            'status' => Transport::STATUS_IN_REVIEW,
            'submitted_for_approval_at' => now(),
            'step7_publish' => 1,
        ]);

        return back()->with('success', 'Transport submitted for admin approval. You will be notified once it is approved.');
    }

    // ════════════════════════════════════════════════════════════════════════
    // Bookings
    // ════════════════════════════════════════════════════════════════════════

    public function allBookingsList()
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            abort(403);
        }

        // Get all transports for this operator
        $transports = $operator->transports()->pluck('id');

        // Get bookings for all transports
        $bookings = TransportBooking::whereIn('transport_id', $transports)
            ->with(['transport', 'travelerAccount'])
            ->orderBy('booked_at', 'desc')
            ->paginate(20);

        return view('operator.transport.all-bookings', compact('bookings'));
    }

    public function bookingList(Transport $transport)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator || $transport->operator_id !== $operator->id) {
            abort(403);
        }

        $bookings = $transport->bookings()
            ->orderBy('booked_at', 'desc')
            ->paginate(20);

        return view('operator.transport.bookings', compact('transport', 'bookings'));
    }

    public function bookingDetails($transportId, $bookingId)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            abort(403);
        }

        $transport = Transport::findOrFail($transportId);
        if ($transport->operator_id !== $operator->id) {
            abort(403);
        }

        $booking = \App\Models\TransportBooking::findOrFail($bookingId);
        if ($booking->transport_id !== $transport->id) {
            abort(403);
        }

        return view('operator.transport.booking-details', compact('transport', 'booking'));
    }

    /**
     * Update booking status for transport bookings
     */
    public function updateBookingStatus(Request $request, $bookingId)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            abort(403);
        }

        // Transports are owned by operators; do not query a non-existent `business_id` column.
        $transportIds = Transport::where('operator_id', $operator->id)
            ->pluck('id');

        $booking = TransportBooking::whereIn('transport_id', $transportIds)
            ->where('id', $bookingId)
            ->firstOrFail();

        $request->validate([
            'booking_status' => 'required|in:Confirmed,Cancelled',
        ]);

        if ($booking->booking_status === 'Cancelled') {
            return back()->with('error', 'Cancelled bookings cannot be updated.');
        }

        $booking->booking_status = $request->input('booking_status');
        $booking->save();

        (new \App\Services\OperatorBookingNotificationService())->notifyBookingStatusChanged(
            $booking,
            'transport',
            $booking->booking_status
        );

        return back()->with('success', 'Booking status updated to ' . $booking->booking_status . '.');
    }

    /**
     * Get available drivers for assignment
     */
    public function getAvailableDriversForBooking($bookingId)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $booking = TransportBooking::findOrFail($bookingId);
        $transport = Transport::findOrFail($booking->transport_id);
        
        if ($transport->operator_id !== $operator->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get operator's drivers
        $driverQuery = OperatorDriver::query()
            ->where('driver_status', 'Active')
            ->where(function ($query) use ($operator) {
                $query->where('operator_id', $operator->operator_id);

                if (!empty($operator->business_id)) {
                    $query->orWhere('business_id', $operator->business_id);
                }
            })
            ->where(function ($query) {
                $query->whereNull('license_expiry_date')
                      ->orWhere('license_expiry_date', '>=', now()->toDateString());
            });

        $drivers = $driverQuery->get([
            'id',
            'driver_name',
            'driver_mobile_no as driver_phone',
            'email as driver_email',
        ]);

        $assignedDriverIds = $booking->drivers()->pluck('operator_drivers.id')->toArray();

        return response()->json([
            'drivers' => $drivers,
            'assigned_driver_ids' => $assignedDriverIds,
        ]);
    }

    /**
     * Assign multiple drivers to a booking
     */
    public function assignDrivers(Request $request, TransportBooking $booking)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $transport = Transport::findOrFail($booking->transport_id);
        if ($transport->operator_id !== $operator->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'driver_ids' => 'required|array|min:1',
            'driver_ids.*' => 'integer|exists:operator_drivers,id',
        ]);

        // Verify all drivers belong to this operator
        $validDriverIds = OperatorDriver::where('operator_id', $operator->operator_id)
            ->whereIn('id', $request->input('driver_ids'))
            ->pluck('id')
            ->toArray();

        if (count($validDriverIds) !== count($request->input('driver_ids'))) {
            return response()->json(['error' => 'Invalid driver selection'], 422);
        }

        // Sync drivers (replaces existing assignments)
        $booking->drivers()->sync($validDriverIds);

        return response()->json([
            'success' => true,
            'message' => 'Drivers assigned successfully',
        ]);
    }

    /**
     * Get assigned drivers for a booking
     */
    public function getBookingDrivers(TransportBooking $booking)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $transport = Transport::findOrFail($booking->transport_id);
        if ($transport->operator_id !== $operator->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $drivers = $booking->drivers()
            ->get([
                'operator_drivers.id as id',
                'operator_drivers.driver_name',
                'operator_drivers.driver_mobile_no as driver_phone',
                'operator_drivers.email as driver_email',
            ])
            ->toArray();

        return response()->json(['drivers' => $drivers]);
    }
}
