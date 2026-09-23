<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Group;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\BookingLineItem;
use App\Models\Traveller;
use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\TransportBooking;
use App\Models\AccommodationRoom;
use App\Models\Activity;
use App\Models\Transport;
use Carbon\Carbon;
use App\Services\PackagePricingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ClosedGroupBookingController extends Controller
{
    public function create()
    {
        // show available closed groups to admin
        $groups = Group::orderBy('available_from', 'desc')->get();
        return view('admin.closed_groups.book', ['groups' => $groups]);
    }

    /**
     * Compute admin-visible total for a closed group given pax (AJAX helper)
     */
    public function price(Group $group, Request $request)
    {
        $pax = (int) ($request->get('pax') ?? 0);
        $pax = max(1, $pax);
        // Return a detailed breakdown using the canonical pricing service so admin UI
        // can display per-service rows (Accommodation, Activity, Transport).
        Log::channel('closed_group')->info('Admin closed-group price called', ['payload_keys' => array_keys($request->all())]);
        try {
            Log::channel('closed_group')->info('Admin closed-group price inputs', ['group_id' => $request->input('group_id'), 'pax' => $request->input('pax'), 'has_selected_rooms' => $request->has('selected_rooms')]);

            // Allow admin UI to pass selected_rooms and guest_assignments so pricing reflects admin choices
            $rawItinerary = is_array($group->itinerary ?? null) ? $group->itinerary : [];
            $itinerary = $rawItinerary;
            $postedRooms = $request->input('selected_rooms', []);
            $postedAssignments = $request->input('guest_assignments', []);

            if (!empty($postedRooms) && is_array($postedRooms)) {
                foreach ($postedRooms as $r) {
                    $d = isset($r['day_index']) ? (int) $r['day_index'] : null;
                    if ($d === null) continue;
                    if (!isset($itinerary[$d]) || !is_array($itinerary[$d])) $itinerary[$d] = [];
                    $itinerary[$d]['accommodation'] = $r['accommodation_id'] ?? ($itinerary[$d]['accommodation'] ?? null);
                    if (!isset($itinerary[$d]['rooms']) || !is_array($itinerary[$d]['rooms'])) $itinerary[$d]['rooms'] = [];
                    $rid = isset($r['room_id']) ? (int) $r['room_id'] : null;
                    if ($rid) $itinerary[$d]['rooms'][] = $rid;
                }
            }

            if (!empty($postedAssignments) && is_array($postedAssignments)) {
                foreach ($postedAssignments as $key => $assigned) {
                    $parts = explode('_', (string) $key, 2);
                    if (count($parts) !== 2) continue;
                    $dayIndex = (int) $parts[0];
                    if (!isset($itinerary[$dayIndex]) || !is_array($itinerary[$dayIndex])) $itinerary[$dayIndex] = [];
                    if (!isset($itinerary[$dayIndex]['guest_assignments'])) $itinerary[$dayIndex]['guest_assignments'] = [];
                    $itinerary[$dayIndex]['guest_assignments'][$key] = is_array($assigned) ? $assigned : explode(',', (string) $assigned);
                }
            }

            // Build a temporary group-like object with overridden itinerary for pricing
            $groupData = $group->toArray();
            $groupData['itinerary'] = $itinerary;
            $tmpGroup = (object) $groupData;

            $svc = new PackagePricingService();
            $breakdown = $svc->calculateGroupTotalDetailed($tmpGroup, $pax, 0, 0);
            return response()->json(['total' => $breakdown['total'] ?? 0, 'items' => $breakdown['items'] ?? []]);
        } catch (\Exception $e) {
            // fallback to numeric total if breakdown fails
            $total = $this->resolveAdminGroupTotalAmount($group, $pax);
            return response()->json(['total' => $total, 'items' => []]);
        }
    }

    /**
     * Minimal booking reference generator for admin-created bookings
     */
    protected function generateBookingRef($type = 'misc', $tripId = null, $date = null)
    {
        $prefix = 'REF-' . strtoupper(substr($type, 0, 3));
        return $prefix . '-' . time() . '-' . rand(100, 999);
    }

    protected function normalizeGroupModel($group): ?Group
    {
        if ($group instanceof Group) {
            return $group;
        }

        if (is_array($group)) {
            $id = $group['id'] ?? $group['group_id'] ?? null;
            if ($id !== null && $id !== '') {
                return Group::find((int) $id);
            }
        }

        return null;
    }

    protected function resolveGroupDateValue($group, string $field): ?string
    {
        if (is_array($group)) {
            if (array_key_exists($field, $group) && $group[$field] !== null && $group[$field] !== '') {
                $value = $group[$field];
            } else {
                $model = $this->normalizeGroupModel($group);
                $value = $model ? ($model->{$field} ?? null) : null;
            }
        } else {
            $model = $this->normalizeGroupModel($group);
            $value = $model ? ($model->{$field} ?? null) : null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (is_object($value) && method_exists($value, 'toDateString')) {
            return $value->toDateString();
        }

        if (is_array($value)) {
            return null;
        }

        return (string) $value;
    }

    /**
     * Return rooms grouped by accommodation id for a given group's itinerary
     */
    public function rooms(Group $group)
    {
        $rawItinerary = $group->itinerary ?? [];

        // determine number of days for the group
        // Prefer explicit itinerary length when present so UI reflects the stored day-by-day plan
        if (!empty($rawItinerary)) {
            if (is_array($rawItinerary)) {
                $numDays = count($rawItinerary);
            } elseif (is_object($rawItinerary)) {
                $numDays = count((array) $rawItinerary);
            } else {
                $numDays = 0;
            }
        } else {
            $numDays = null;
            if (!empty($group->no_of_days) && is_numeric($group->no_of_days)) {
                $numDays = (int) $group->no_of_days;
            } elseif (!empty($group->available_from) && !empty($group->available_to)) {
                try {
                    $from = \Carbon\Carbon::parse($group->available_from);
                    $to = \Carbon\Carbon::parse($group->available_to);
                    $numDays = $from->diffInDays($to) + 1;
                } catch (\Exception $e) {
                    $numDays = 0;
                }
            }
        }

        // normalize itinerary into an indexed array of day entries (0-based)
        $itinerary = [];
        for ($i = 0; $i < max(0, $numDays); $i++) {
            $dayRaw = $rawItinerary[$i] ?? null;
            $accomId = null;
            // extract accommodation id from several possible shapes
            if (is_numeric($dayRaw)) {
                $accomId = (int) $dayRaw;
            } elseif (is_array($dayRaw)) {
                if (!empty($dayRaw['accommodation'])) {
                    if (is_numeric($dayRaw['accommodation'])) $accomId = (int) $dayRaw['accommodation'];
                    elseif (is_array($dayRaw['accommodation']) && !empty($dayRaw['accommodation']['id'])) $accomId = (int) $dayRaw['accommodation']['id'];
                } elseif (!empty($dayRaw['accommodation_id'])) {
                    $accomId = (int) $dayRaw['accommodation_id'];
                }
            } elseif (is_object($dayRaw)) {
                if (!empty($dayRaw->accommodation) && is_numeric($dayRaw->accommodation)) $accomId = (int) $dayRaw->accommodation;
                elseif (!empty($dayRaw->accommodation_id)) $accomId = (int) $dayRaw->accommodation_id;
                elseif (!empty($dayRaw->accommodation) && is_object($dayRaw->accommodation) && !empty($dayRaw->accommodation->id)) $accomId = (int) $dayRaw->accommodation->id;
            }
            $itinerary[$i] = [
                'day_index' => $i,
                'accommodation' => $accomId,
                'accommodation_name' => null,
                'title' => is_array($dayRaw) && !empty($dayRaw['title']) ? $dayRaw['title'] : (is_object($dayRaw) && !empty($dayRaw->title) ? $dayRaw->title : null),
            ];
        }

        // collect accommodation ids from normalized itinerary
        $accommodationIds = array_values(array_filter(array_map(function($d){ return $d['accommodation']; }, $itinerary)));
        $accommodationIds = array_values(array_unique(array_filter($accommodationIds)));
        $rooms = [];
        if (!empty($accommodationIds)) {
            $roomModels = \App\Models\AccommodationRoom::whereIn('accommodation_id', $accommodationIds)->get();
            foreach ($roomModels as $r) {
                $rooms[$r->accommodation_id][] = $r;
            }
        }
        // also include accommodation metadata for each accommodation id
        $accommodations = [];
        if (!empty($accommodationIds)) {
            $acModels = \App\Models\Accommodation::with(['rooms.rates'])->whereIn('id', $accommodationIds)->get();
            foreach ($acModels as $ac) {
                $roomList = [];
                foreach ($ac->rooms as $r) {
                    $rates = [];
                    foreach ($r->rates as $rate) {
                        $rates[] = [
                            'id' => $rate->id,
                            'name' => $rate->name ?? ($rate->rate_name ?? 'Rate'),
                            'price' => $rate->price ?? $rate->amount ?? null,
                            'meal_plan' => $rate->meal_plan ?? null,
                        ];
                    }
                    $roomList[] = [
                        'id' => $r->id,
                        'room_id' => $r->room_id ?? null,
                        'room_name' => $r->room_name ?? $r->room_type,
                        'room_type' => $r->room_type,
                        'capacity' => $r->capacity ?? $r->occupancy ?? 1,
                        'quantity' => $r->quantity ?? 1,
                        'base_price' => $r->base_price ?? null,
                        'rates' => $rates,
                    ];
                }

                $accommodations[$ac->id] = [
                    'id' => $ac->id,
                    'name' => $ac->property_name ?? $ac->name ?? ($ac->title ?? 'Accommodation'),
                    'place' => $ac->place ?? $ac->location ?? null,
                    'rooms' => $roomList,
                ];
            }
        }

        // enrich itinerary with accommodation_name where possible
        foreach ($itinerary as $idx => $day) {
            $aid = $day['accommodation'];
            if ($aid && isset($accommodations[$aid])) {
                $itinerary[$idx]['accommodation_name'] = $accommodations[$aid]['name'];
            }
        }

        return response()->json(['rooms' => $rooms, 'itinerary' => $itinerary, 'accommodations' => $accommodations]);
    }

    protected function resolveAdminGroupTotalAmount(Group $group, int $pax): float
    {
        // Use the canonical pricing service so admin totals match frontend behaviour.
        // Admin closed-group bookings treat all travellers as adults (children/infants not allowed),
        // so pass children=0 and infants=0 to the service.
        $pricing = new PackagePricingService();
        try {
            $total = $pricing->calculateGroupTotal($group, max(1, (int) $pax), 0, 0);
            return round((float) $total, 2);
        } catch (\Exception $e) {
            \Log::error('Failed to compute admin group total via PackagePricingService: ' . $e->getMessage());
            return 0.0;
        }
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'group_id' => 'required|integer|exists:groups,id',
            'lead_first_name' => 'required|string|max:255',
            'lead_last_name' => 'nullable|string|max:255',
            'lead_email' => 'nullable|email|max:255',
            'lead_phone' => 'nullable|string|max:50',
            'pax' => 'required|integer|min:1',
            'total_amount' => 'nullable|numeric|min:0',
            'guests' => 'nullable|array',
            'selected_rooms' => 'nullable|array',
            'guest_assignments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        $group = Group::find($data['group_id'] ?? null);
        if (!$group && !empty($request->input('group')) && is_array($request->input('group'))) {
            $group = Group::find($request->input('group.id') ?? $request->input('group.group_id') ?? null);
        }

        if (!$group) {
            throw new \RuntimeException('Closed group not found.');
        }

        $computedTotal = isset($data['total_amount']) && $data['total_amount'] !== ''
            ? (float) $data['total_amount']
            : $this->resolveAdminGroupTotalAmount($group, (int) $data['pax']);

        DB::beginTransaction();
        try {
            Log::channel('closed_group')->info('Creating Trip record', ['group_id' => $group->id, 'title' => $group->name ?? null]);
            $groupStartDate = $this->resolveGroupDateValue($group, 'available_from');
            $groupEndDate = $this->resolveGroupDateValue($group, 'available_to');

            $trip = Trip::create([
                'traveler_account_id' => null,
                'title' => $group->name ?? 'Closed Group Booking',
                'start_date' => $groupStartDate,
                'end_date' => $groupEndDate,
                'status' => 'planned',
            ]);

            Log::channel('closed_group')->info('Trip created', ['trip_id' => $trip->id]);

            Log::channel('closed_group')->info('Creating Booking record', ['trip_id' => $trip->id, 'total_amount' => $computedTotal]);
            $booking = Booking::create([
                'trip_id' => $trip->id,
                'operator_id' => null,
                'total_amount' => $computedTotal,
                'status' => 'pending',
                'booking_type' => 'close-group',
            ]);

            Log::channel('closed_group')->info('Booking created', ['booking_id' => $booking->id]);

            BookingLineItem::create([
                'booking_id' => $booking->id,
                // use 'package' to match booking_line_items.service_type enum
                'service_type' => 'package',
                'service_id' => $group->id,
                'quantity' => max(1, (int) $data['pax']),
                'price' => $computedTotal,
                'start_date' => $groupStartDate,
                'end_date' => $groupEndDate,
                'status' => 'active',
            ]);
            $leadFullName = trim(($data['lead_first_name'] ?? '') . ' ' . ($data['lead_last_name'] ?? ''));
            Traveller::create([
                'trip_id' => $trip->id,
                'name' => $leadFullName,
                'email' => $data['lead_email'] ?? null,
                'phone' => $data['lead_phone'] ?? null,
                'relationship' => 'lead',
            ]);

            if (!empty($data['guests']) && is_array($data['guests'])) {
                foreach ($data['guests'] as $g) {
                    $full = trim(($g['first_name'] ?? '') . ' ' . ($g['last_name'] ?? ''));
                    if ($full !== '') {
                        Traveller::create([
                            'trip_id' => $trip->id,
                            'name' => $full,
                            'email' => $g['email'] ?? null,
                            'phone' => $g['phone'] ?? null,
                            'date_of_birth' => $g['dob'] ?? null,
                            'relationship' => $g['relation'] ?? 'guest',
                        ]);
                    }
                }
            }
            $itinerary = is_array($group->itinerary ?? null) ? $group->itinerary : [];
            // pricing service for computing authoritative per-service amounts
            $pricingService = new PackagePricingService();

            // If admin provided selected_rooms / guest_assignments in the booking form, merge them
            $postedRooms = $request->input('selected_rooms', []);
            $postedAssignments = $request->input('guest_assignments', []);
            if (!empty($postedRooms) && is_array($postedRooms)) {
                foreach ($postedRooms as $r) {
                    $d = isset($r['day_index']) ? (int) $r['day_index'] : null;
                    if ($d === null) continue;
                    if (!isset($itinerary[$d]) || !is_array($itinerary[$d])) $itinerary[$d] = [];
                    $itinerary[$d]['accommodation'] = $r['accommodation_id'] ?? ($itinerary[$d]['accommodation'] ?? null);
                    if (!isset($itinerary[$d]['rooms']) || !is_array($itinerary[$d]['rooms'])) $itinerary[$d]['rooms'] = [];
                    $rid = isset($r['room_id']) ? (int) $r['room_id'] : null;
                    if ($rid) $itinerary[$d]['rooms'][] = $rid;
                }
            }

            // attach guest assignments map for later use (optional)
            if (!empty($postedAssignments) && is_array($postedAssignments)) {
                foreach ($postedAssignments as $key => $assigned) {
                    // key expected as "{dayIndex}_{roomId}" — store under itinerary for lookup
                    $parts = explode('_', (string) $key, 2);
                    if (count($parts) !== 2) continue;
                    $dayIndex = (int) $parts[0];
                    if (!isset($itinerary[$dayIndex]) || !is_array($itinerary[$dayIndex])) $itinerary[$dayIndex] = [];
                    if (!isset($itinerary[$dayIndex]['guest_assignments'])) $itinerary[$dayIndex]['guest_assignments'] = [];
                    $itinerary[$dayIndex]['guest_assignments'][$key] = is_array($assigned) ? $assigned : explode(',', (string) $assigned);
                }
            }
            $createdAccommodationKeys = [];
            foreach ($itinerary as $dayIndex => $dayEntry) {
                Log::channel('closed_group')->info('Processing itinerary day', ['day_index' => $dayIndex, 'day_entry' => $dayEntry]);
                if (!is_array($dayEntry)) {
                    continue;
                }

                $dayDate = $groupStartDate ? Carbon::parse($groupStartDate)->addDays((int) $dayIndex) : null;

                if (!empty($dayEntry['accommodation'])) {
                    $accommodationId = (int) $dayEntry['accommodation'];
                    $selectedRoomIds = array_values(array_filter(array_map('intval', (array) ($dayEntry['rooms'] ?? []))));
                    foreach ($selectedRoomIds as $roomId) {
                        Log::channel('closed_group')->info('Creating AccommodationBooking attempt', ['trip_id' => $trip->id, 'room_id' => $roomId, 'accommodation_id' => $accommodationId]);
                        $room = AccommodationRoom::find($roomId);
                        if (!$room) {
                            Log::channel('closed_group')->error('AccommodationRoom not found', ['room_id' => $roomId, 'accommodation_id' => $accommodationId]);
                            continue;
                        }
                        $checkIn = $dayDate ? $dayDate->toDateString() : null;
                        $checkOut = $checkIn ? Carbon::parse($checkIn)->addDay()->toDateString() : null;

                        // Compute accommodation amount using canonical pricing
                        $accommodationModel = \App\Models\Accommodation::find($accommodationId);
                        $accAmount = 0.0;
                        try {
                            if ($accommodationModel) {
                                $accAmount = $pricingService->getAccommodationAmount($accommodationModel, is_array($dayEntry) ? $dayEntry : [], $group, max(1, (int) $data['pax']), 0, 0);
                            }
                        } catch (\Exception $e) {
                            Log::channel('closed_group')->error('Failed to compute accommodation amount', ['err' => $e->getMessage(), 'accommodation_id' => $accommodationId]);
                        }

                        // determine adults/children for this room using explicit guest_assignments when available
                        $assignedKey = $dayIndex . '_' . $roomId;
                        $assignedForRoom = [];
                        if (!empty($dayEntry['guest_assignments']) && is_array($dayEntry['guest_assignments'])) {
                            $assignedForRoom = $dayEntry['guest_assignments'][$assignedKey] ?? [];
                            if (!is_array($assignedForRoom) && is_string($assignedForRoom)) {
                                $assignedForRoom = array_filter(array_map('trim', explode(',', $assignedForRoom)));
                            }
                        }

                        // fallback logic: if explicit assignments exist use their count, otherwise distribute pax across selected rooms
                        $adultsCount = 0;
                        if (!empty($assignedForRoom) && is_array($assignedForRoom)) {
                            $adultsCount = count(array_filter($assignedForRoom, fn($v) => trim((string)$v) !== ''));
                        }
                        if ($adultsCount <= 0) {
                            $roomsCount = max(1, count($selectedRoomIds));
                            if ($roomsCount === 1) {
                                $adultsCount = max(1, (int) $data['pax']);
                            } else {
                                $adultsCount = max(1, (int) ceil((int) $data['pax'] / $roomsCount));
                            }
                        }

                        $accData = [
                            'booking_reference' => $this->generateBookingRef('accommodation', $trip->id, $checkIn ?? now()->toDateString()),
                            'accommodation_id' => $accommodationId,
                            'room_id' => $roomId,
                            'guest_name' => $leadFullName,
                            'traveler_account_id' => null,
                            'traveler_relation' => 'self',
                            'guest_email' => $data['lead_email'] ?? null,
                            'check_in_date' => $checkIn,
                            'check_out_date' => $checkOut,
                            'rooms_booked' => 1,
                            'adults' => (int) $adultsCount,
                            'children' => 0,
                            'booking_status' => 'Pending',
                            'total_amount' => (float) round($accAmount, 2),
                            'currency' => 'USD',
                            'source_channel' => 'Admin',
                            'booked_at' => now(),
                            'trip_id' => $trip->id,
                        ];
                        if (Schema::hasColumn('accommodation_bookings', 'payment_method')) {
                            $accData['payment_method'] = 'Bank Transfer';
                        }
                        $accKey = $accommodationId . '|' . ($accData['check_in_date'] ?? '');
                        if (in_array($accKey, $createdAccommodationKeys, true)) {
                            Log::channel('closed_group')->info('Skipping duplicate AccommodationBooking', ['key' => $accKey]);
                            continue;
                        }
                        $acc = AccommodationBooking::create($accData);
                        $createdAccommodationKeys[] = $accKey;
                        Log::channel('closed_group')->info('AccommodationBooking created', ['id' => $acc->id ?? null, 'room_id' => $roomId]);
                    }
                }

                if (!empty($dayEntry['activity'])) {
                    $activity = Activity::find((int) $dayEntry['activity']);
                    if ($activity) {
                        $activityName = $activity->name ?? $activity->title ?? $activity->activity_name ?? null;
                        if (empty(trim((string) ($activityName ?? '')))) {
                            Log::channel('closed_group')->info('Skipping ActivityBooking because activity has no name', ['activity_id' => $activity->id, 'day_index' => $dayIndex]);
                            // skip creation when activity lacks a proper name
                        } else {
                        Log::channel('closed_group')->info('Creating ActivityBooking attempt', ['trip_id' => $trip->id, 'activity_id' => $activity->id, 'day_index' => $dayIndex]);
                        // Determine variant id/name: prefer explicit keys but fall back to activity_selection entries
                        $variantId = $dayEntry['variant_id'] ?? null;
                        $variantName = $dayEntry['variant_name'] ?? null;
                        if (empty($variantId) && !empty($dayEntry['activity_selection'])) {
                            $sel = $dayEntry['activity_selection'];
                            if (is_array($sel)) $first = reset($sel); else $first = $sel;
                            if (!empty($first) && strpos($first, '|') !== false) {
                                [$vid, $opt] = explode('|', $first, 2);
                                $variantId = trim((string) $vid);
                            } else {
                                $variantId = trim((string) $first);
                            }
                            if (!empty($variantId)) {
                                $variantModel = \App\Models\ActivityVariant::find($variantId);
                                if ($variantModel) $variantName = $variantModel->variant_name ?? $variantName;
                            }
                        }

                        // Compute activity amount using canonical pricing
                        $activityAmount = 0.0;
                        try {
                            $activityCount = max(1, (int) $data['pax']);
                            $activityAmount = $pricingService->getActivityAmount($activity, is_array($dayEntry) ? $dayEntry : [], $activityCount, $group);
                        } catch (\Exception $e) {
                            Log::channel('closed_group')->error('Failed to compute activity amount', ['err' => $e->getMessage(), 'activity_id' => $activity->id ?? null]);
                        }

                        $abData = [
                            'booking_reference' => $this->generateBookingRef('activity', $trip->id, $dayDate?->toDateString() ?? now()->toDateString()),
                            'activity_id' => $activity->id,
                            'variant_id' => $variantId,
                            'variant_name' => $variantName,
                            'guest_name' => $leadFullName,
                            'guest_email' => $data['lead_email'] ?? null,
                            'guest_phone' => $data['lead_phone'] ?? null,
                            'activity_date' => $dayDate?->toDateString() ?? now()->toDateString(),
                            'adults' => max(1, (int) $data['pax']),
                            'children' => 0,
                            'booking_status' => 'Pending',
                            'total_amount' => (float) round($activityAmount, 2),
                            'currency' => 'USD',
                            'source_channel' => 'Admin',
                            'special_requests' => null,
                            'booked_at' => now(),
                            'trip_id' => $trip->id,
                        ];
                        if (Schema::hasColumn('activity_bookings', 'payment_method')) {
                            $abData['payment_method'] = 'Bank Transfer';
                        }
                            $ab = ActivityBooking::create($abData);
                            Log::channel('closed_group')->info('ActivityBooking created', ['id' => $ab->id ?? null, 'activity_id' => $activity->id, 'variant_id' => $variantId]);
                        }
                    }
                }

                if (!empty($dayEntry['transport']) || !empty($dayEntry['transport_schedule'])) {
                    $transportId = (int) ($dayEntry['transport'] ?? 0);
                    $transport = $transportId ? Transport::with('routes')->find($transportId) : null;
                    if ($transport) {
                        Log::channel('closed_group')->info('Creating TransportBooking attempts', ['trip_id' => $trip->id, 'transport_id' => $transport->id]);

                        // extract selected route groups (route_ids + add_return) using the same logic as frontend
                        $selectedGroups = $this->extractSelectedRouteGroups(is_array($dayEntry) ? $dayEntry : []);

                        $routesToBook = [];
                        if (!empty($selectedGroups)) {
                            foreach ($selectedGroups as $groupMeta) {
                                $addReturn = !empty($groupMeta['add_return']);
                                foreach ($groupMeta['route_ids'] as $rid) {
                                    // support when route id entries are objects/arrays (sometimes itinerary stores full route objects)
                                    if (is_object($rid) && ($rid instanceof \App\Models\TransportRoute)) {
                                        $found = $rid;
                                        $routesToBook[] = ['route' => $found, 'add_return' => $addReturn];
                                        continue;
                                    }
                                    if (is_array($rid)) {
                                        $ridVal = $rid['id'] ?? $rid['route_id'] ?? null;
                                    } else {
                                        $ridVal = $rid;
                                    }
                                    // find matching route in transport->routes by route_id or id
                                    $found = null;
                                    foreach ($transport->routes as $rt) {
                                        $rtKey = $this->normalizeTransportRouteKey((string) ($rt->route_id ?? $rt->id ?? ''));
                                        $normalizedRid = $this->normalizeTransportRouteKey((string) ($ridVal ?? ''));
                                        if ($rtKey !== '' && ($rtKey === $normalizedRid || (string) ($rt->id ?? '') === (string) ($ridVal ?? ''))) {
                                            $found = $rt;
                                            break;
                                        }
                                    }
                                    if ($found) $routesToBook[] = ['route' => $found, 'add_return' => $addReturn];
                                }
                            }
                        }

                        // fallback: if nothing matched, try first route
                        if (empty($routesToBook)) {
                            $firstRoute = $transport->routes->first();
                            if ($firstRoute) $routesToBook[] = $firstRoute;
                        }

                        foreach ($routesToBook as $routeEntry) {
                            $route = $routeEntry['route'] ?? null;
                            $wantReturn = !empty($routeEntry['add_return']);
                            if (!$route) {
                                Log::channel('closed_group')->warning('Transport route not found for selected id', ['route_entry' => $routeEntry]);
                                continue;
                            }
                            $pricing = is_array($route->pricing ?? null) ? $route->pricing : [];
                            // compute amount for this route using the canonical pricing service
                            $passengers = max(1, (int) $data['pax']);
                            $routeAmount = 0.0;
                            try {
                                $routeAmount = $pricingService->getGroupTransportRouteAmount($route, $passengers, $group, $wantReturn);
                            } catch (\Exception $e) {
                                try {
                                    $routeAmount = $pricingService->getTransportRouteAmount($route, $passengers, $group, $wantReturn);
                                } catch (\Exception $e2) {
                                    Log::channel('closed_group')->error('Failed to compute transport route amount', ['err' => $e2->getMessage(), 'route_id' => $route->id ?? null]);
                                }
                            }

                            $tbData = [
                                'transport_id' => $transport->id,
                                'traveler_account_id' => null,
                                'booking_reference' => $this->generateBookingRef('transport', $trip->id, $dayDate?->toDateString() ?? now()->toDateString()),
                                'guest_name' => $leadFullName,
                                'guest_email' => $data['lead_email'] ?? null,
                                'guest_phone' => $data['lead_phone'] ?? null,
                                'route_from' => $route->route_from ?? '',
                                'route_to' => $route->route_to ?? '',
                                'pickup_date' => $dayDate?->toDateString() ?? now()->toDateString(),
                                'pickup_time' => null,
                                'return_date' => $dayDate?->copy()->addDay()->toDateString(),
                                'return_time' => null,
                                'adults' => max(1, (int) $data['pax']),
                                'children' => 0,
                                'total_passengers' => max(1, (int) $data['pax']),
                                'traveler_first_name' => $data['lead_first_name'] ?? null,
                                'traveler_middle_name' => null,
                                'traveler_last_name' => $data['lead_last_name'] ?? null,
                                'traveler_relation' => 'self',
                                'price_per_person' => 0.0,
                                'total_amount' => (float) round($routeAmount, 2),
                                'currency' => 'USD',
                                'booking_status' => TransportBooking::STATUS_PROCESSING,
                                'payment_method' => Schema::hasColumn('transport_bookings', 'payment_method') ? 'Bank Transfer' : null,
                                'source_channel' => 'Admin',
                                'trip_id' => $trip->id,
                                'booked_at' => now(),
                            ];
                            $tb = TransportBooking::create($tbData);
                            Log::channel('closed_group')->info('TransportBooking created', ['id' => $tb->id ?? null, 'route_id' => $route->id]);
                        }
                    }
                }
            }

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'booking_id' => $booking->id]);
            }

            return redirect()->route('admin.closed-groups.book')->with('success', 'Closed group booking created. REF: ' . $booking->id);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Admin closed group booking create failed: ' . $e->getMessage());
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to create booking: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Failed to create booking: ' . $e->getMessage());
        }
    }

    private function normalizeTransportRouteKey(string $raw): string
    {
        $s = trim((string) $raw);
        if ($s === '') return '';
        $s = preg_replace('/[^A-Za-z0-9\-_.]/', '-', $s);
        $s = preg_replace('/-+/', '-', $s);
        return strtolower(trim($s, '-'));
    }

    private function extractSelectedRouteGroups(array $dayEntry): array
    {
        $groups = [];

        if (!empty($dayEntry['transport_schedule']) && is_array($dayEntry['transport_schedule'])) {
            foreach ($dayEntry['transport_schedule'] as $svcGroup) {
                if (!is_array($svcGroup)) continue;

                foreach ($svcGroup as $routeKey => $routeData) {
                    $isSelected = false;
                    if (is_array($routeData)) {
                        $isSelected = !empty($routeData['selected']) || !empty($routeData['selected_route']) || !empty($routeData['route_id']);
                    } elseif (!is_array($routeData) && $routeData !== null && $routeData !== false) {
                        $isSelected = true;
                    }

                    if (!$isSelected) continue;

                    $routeId = null;
                    if (is_array($routeData)) {
                        $routeId = $routeData['route_id'] ?? $routeData['selected_route'] ?? $routeData['id'] ?? null;
                    }

                    if ($routeId === null && is_string($routeKey) && trim((string) $routeKey) !== '') {
                        $routeId = $routeKey;
                    }

                    $baseKey = is_string($routeKey) ? preg_replace('/-(fwd|rev)$/i', '', (string) $routeKey) : (string) $routeId;
                    $baseKey = trim((string) $baseKey);
                    if ($baseKey === '') continue;

                    if (!isset($groups[$baseKey])) {
                        $groups[$baseKey] = ['route_ids' => [], 'add_return' => false, 'directions' => []];
                    }

                    $resolvedRouteId = $routeId ?? $baseKey;
                    $resolvedRouteId = trim((string) $resolvedRouteId);
                    if ($resolvedRouteId !== '') {
                        $normalizedRouteId = $this->normalizeTransportRouteKey($resolvedRouteId);
                        if ($normalizedRouteId !== '' && !in_array($normalizedRouteId, array_map('strval', $groups[$baseKey]['route_ids']), true)) {
                            $groups[$baseKey]['route_ids'][] = $normalizedRouteId;
                        }
                    }

                    if (is_string($routeKey) && preg_match('/-(fwd|rev)$/i', $routeKey)) {
                        $direction = strtolower(substr($routeKey, -3));
                        if (!in_array($direction, $groups[$baseKey]['directions'], true)) {
                            $groups[$baseKey]['directions'][] = $direction;
                        }
                    }
                }
            }
        }

        if (empty($groups)) {
            $possibleKeys = ['transport_routes', 'transport_route_ids', 'routes', 'selected_routes', 'selected_transport_routes', 'route_ids'];
            foreach ($possibleKeys as $k) {
                if (empty($dayEntry[$k])) continue;
                $values = is_array($dayEntry[$k]) ? $dayEntry[$k] : [$dayEntry[$k]];
                foreach ($values as $value) {
                    if ($value === null || $value === false || $value === '') continue;
                    $normalized = trim((string) $value);
                    $baseKey = $this->normalizeTransportRouteKey($normalized);
                    if ($baseKey === '') continue;
                    if (!isset($groups[$baseKey])) {
                        $groups[$baseKey] = ['route_ids' => [], 'add_return' => false, 'directions' => []];
                    }
                    if (!in_array($normalized, array_map('strval', $groups[$baseKey]['route_ids']), true)) {
                        $groups[$baseKey]['route_ids'][] = $normalized;
                    }
                }
            }
        }

        foreach ($groups as $baseKey => $meta) {
            $groups[$baseKey]['add_return'] = isset($meta['directions']) && count(array_unique($meta['directions'])) > 1;
        }

        return array_values(array_map(function ($routeIds, $meta) {
            return ['route_ids' => array_values(array_unique(array_filter(array_map('strval', $routeIds), fn ($value) => trim((string) $value) !== ''))), 'add_return' => (bool) $meta['add_return']];
        }, array_map(fn ($group) => $group['route_ids'], $groups), $groups));
    }
}
