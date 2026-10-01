<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Group;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\BookingLineItem;
use App\Models\BookingRef;
use App\Models\BliTravellerAllocation;
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

class ClosedGroupBookingController extends Controller
{
    public function create()
    {
        // show available closed groups to admin
        $groups = Group::orderBy('available_from', 'desc')->get();
        return view('admin.closed_groups.book', ['groups' => $groups]);
    }

    public function csrfToken()
    {
        return response()->json(['token' => csrf_token()]);
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
            $selectedRoomDays = [];

            if (!empty($postedRooms) && is_array($postedRooms)) {
                foreach ($postedRooms as $r) {
                    $d = isset($r['day_index']) ? (int) $r['day_index'] : null;
                    if ($d === null) continue;
                    if (!isset($itinerary[$d]) || !is_array($itinerary[$d])) $itinerary[$d] = [];
                    $itinerary[$d]['accommodation'] = $r['accommodation_id'] ?? ($itinerary[$d]['accommodation'] ?? null);
                    if (!isset($selectedRoomDays[$d])) {
                        $itinerary[$d]['rooms'] = [];
                        $selectedRoomDays[$d] = true;
                    } elseif (!isset($itinerary[$d]['rooms']) || !is_array($itinerary[$d]['rooms'])) {
                        $itinerary[$d]['rooms'] = [];
                    }
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

            $breakdown = $this->calculateAdminGroupPriceBreakdown($group, $itinerary, $pax);
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
        $prefix = match ($type) {
            'accommodation' => 'ACC',
            'activity' => 'ACT',
            'transport' => 'TRS',
            default => 'PKG',
        };
        $datePart = $date ? Carbon::parse($date)->format('Ymd') : now()->format('Ymd');
        $count = match ($type) {
            'accommodation' => AccommodationBooking::where('trip_id', $tripId)->count(),
            'activity' => ActivityBooking::where('trip_id', $tripId)->count(),
            'transport' => TransportBooking::where('trip_id', $tripId)->count(),
            default => Booking::where('trip_id', $tripId)->count(),
        };
        $candidate = sprintf('%s-%s-%s-%d', $prefix, $tripId ?: 'GUEST', $datePart, $count + 1);
        $suffix = 1;
        $exists = fn (string $reference): bool => match ($type) {
            'accommodation' => AccommodationBooking::where('booking_reference', $reference)->exists(),
            'activity' => ActivityBooking::where('booking_reference', $reference)->exists(),
            'transport' => TransportBooking::where('booking_reference', $reference)->exists(),
            default => Booking::where('id', $reference)->exists(),
        };
        while ($exists($candidate)) {
            $candidate = sprintf('%s-%s-%s-%d-%02d', $prefix, $tripId ?: 'GUEST', $datePart, $count + 1, $suffix++);
        }

        return $candidate;
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
            $allocatedRoomIds = is_array($dayRaw) ? array_values(array_filter(array_map('intval', (array) ($dayRaw['rooms'] ?? [])))) : [];
            $itinerary[$i] = [
                'day_index' => $i,
                'accommodation' => $accomId,
                'accommodation_name' => null,
                'rooms' => [],
                'title' => is_array($dayRaw) && !empty($dayRaw['title']) ? $dayRaw['title'] : (is_object($dayRaw) && !empty($dayRaw->title) ? $dayRaw->title : null),
            ];
            $itinerary[$i]['allocated_room_ids'] = $allocatedRoomIds;
        }

        // Collect only room types allocated to this group for each itinerary day.
        $accommodationIds = array_values(array_filter(array_map(function($d){ return $d['accommodation']; }, $itinerary)));
        $accommodationIds = array_values(array_unique(array_filter($accommodationIds)));
        $rooms = [];
        foreach ($itinerary as $dayIndex => $day) {
            $allocatedRoomIds = $day['allocated_room_ids'];
            if ($day['accommodation'] && !empty($allocatedRoomIds)) {
                $roomModels = AccommodationRoom::with('rates')
                    ->where('accommodation_id', $day['accommodation'])
                    ->whereIn('id', $allocatedRoomIds)
                    ->get();
                foreach ($roomModels as $room) {
                    $rates = [];
                    foreach ($room->rates as $rate) {
                        $rates[] = [
                            'id' => $rate->id,
                            'name' => $rate->name ?? ($rate->rate_name ?? 'Rate'),
                            'price' => $rate->price ?? $rate->amount ?? null,
                            'meal_plan' => $rate->meal_plan ?? null,
                        ];
                    }
                    $rooms[$dayIndex][] = [
                        'id' => $room->id,
                        'room_id' => $room->room_id ?? null,
                        'room_name' => $room->room_name ?? $room->room_type,
                        'room_type' => $room->room_type,
                        'capacity' => $room->capacity ?? $room->occupancy ?? 1,
                        'occupancy' => $room->occupancy ?? $room->capacity ?? 1,
                        'quantity' => $room->quantity ?? 1,
                        'base_price' => $room->base_price ?? null,
                        'accommodation_id' => $room->accommodation_id,
                        'rates' => $rates,
                    ];
                }
            }
            $rooms[$dayIndex] = $rooms[$dayIndex] ?? [];
            $itinerary[$dayIndex]['rooms'] = $rooms[$dayIndex];
        }
        // also include accommodation metadata for each accommodation id
        $accommodations = [];
        if (!empty($accommodationIds)) {
            $acModels = \App\Models\Accommodation::whereIn('id', $accommodationIds)->get();
            foreach ($acModels as $ac) {
                $accommodations[$ac->id] = [
                    'id' => $ac->id,
                    'name' => $ac->property_name ?? $ac->name ?? ($ac->title ?? 'Accommodation'),
                    'place' => $ac->place ?? $ac->location ?? null,
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

    protected function calculateAdminGroupPriceBreakdown(Group $group, array $itinerary, int $adults): array
    {
        $groupData = $group->toArray();
        $groupData['itinerary'] = $itinerary;
        $bookingGroup = (object) $groupData;
        $pricingService = new PackagePricingService();
        $breakdown = $pricingService->calculateGroupTotalDetailed($bookingGroup, max(1, $adults), 0, 0);

        $dayNumbers = [];
        $dayNumber = 0;
        foreach ($itinerary as $dayIndex => $dayEntry) {
            if (!is_numeric($dayIndex) || (int) $dayIndex < 0) {
                continue;
            }
            if ((int) ($group->no_of_days ?? 0) > 0 && (int) $dayIndex + 1 > (int) $group->no_of_days) {
                continue;
            }
            if (is_array($dayEntry) && $pricingService->isMeaningfulDayEntry($dayEntry)) {
                $dayNumbers[$dayIndex] = ++$dayNumber;
            }
        }

        foreach ($itinerary as $dayIndex => $dayEntry) {
            if (!is_array($dayEntry) || empty($dayEntry['accommodation']) || empty($dayNumbers[$dayIndex])) {
                continue;
            }
            $roomIds = array_values(array_filter(array_map('intval', (array) ($dayEntry['rooms'] ?? [])), fn ($id) => $id > 0));
            if ($roomIds === []) {
                continue;
            }

            $accommodation = \App\Models\Accommodation::find((int) $dayEntry['accommodation']);
            if (!$accommodation) {
                continue;
            }

            $accommodationAmount = 0.0;
            foreach ($roomIds as $roomId) {
                $roomEntry = $dayEntry;
                $roomEntry['rooms'] = [$roomId];
                $accommodationAmount += $pricingService->getGroupAccommodationAmount(
                    $accommodation,
                    $roomEntry,
                    $bookingGroup,
                    max(1, $adults),
                    0,
                    0
                );
            }

            foreach ($breakdown['items'] as &$item) {
                if ((int) ($item['day'] ?? 0) === (int) $dayNumbers[$dayIndex]
                    && strtolower((string) ($item['type'] ?? '')) === 'accommodation') {
                    $item['amount'] = round($accommodationAmount, 2);
                    break;
                }
            }
            unset($item);
        }

        $breakdown['total'] = round(array_sum(array_map(fn ($item) => (float) ($item['amount'] ?? 0), $breakdown['items'] ?? [])), 2);
        return $breakdown;
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
            'idempotency_key' => 'nullable|uuid',
            'guests' => 'nullable|array',
            'selected_rooms' => 'nullable|array',
            'selected_rooms.*.day_index' => 'required|integer|min:0',
            'selected_rooms.*.accommodation_id' => 'required|integer|min:1',
            'selected_rooms.*.room_id' => 'required|integer|min:1',
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

        $groupItinerary = is_array($group->itinerary ?? null) ? $group->itinerary : [];
        foreach ((array) $request->input('selected_rooms', []) as $index => $selectedRoom) {
            $dayIndex = isset($selectedRoom['day_index']) && is_numeric($selectedRoom['day_index'])
                ? (int) $selectedRoom['day_index']
                : null;
            $roomId = isset($selectedRoom['room_id']) && is_numeric($selectedRoom['room_id'])
                ? (int) $selectedRoom['room_id']
                : null;
            $dayAllocation = $dayIndex !== null ? ($groupItinerary[$dayIndex] ?? null) : null;
            $allocatedAccommodationId = is_array($dayAllocation) ? (int) ($dayAllocation['accommodation'] ?? 0) : 0;
            $allocatedRoomIds = is_array($dayAllocation)
                ? array_values(array_filter(array_map('intval', (array) ($dayAllocation['rooms'] ?? []))))
                : [];
            $selectedAccommodationId = isset($selectedRoom['accommodation_id']) && is_numeric($selectedRoom['accommodation_id'])
                ? (int) $selectedRoom['accommodation_id']
                : 0;

            $roomMatchesAllocation = $roomId !== null
                && in_array($roomId, $allocatedRoomIds, true)
                && $allocatedAccommodationId > 0
                && $selectedAccommodationId === $allocatedAccommodationId
                && AccommodationRoom::where('id', $roomId)
                    ->where('accommodation_id', $allocatedAccommodationId)
                    ->exists();

            if (!$roomMatchesAllocation) {
                return response()->json([
                    'success' => false,
                    'errors' => ["selected_rooms.{$index}.room_id" => ['The selected room type is not allocated to this group day.']],
                ], 422);
            }
        }

        $idempotencyKey = (string) ($data['idempotency_key'] ?? \Illuminate\Support\Str::uuid());
        $existingBookingRef = BookingRef::where('idempotency_key', $idempotencyKey)
            ->orWhere('booking_ref_code', 'like', '%-' . $idempotencyKey)
            ->first();
        if ($existingBookingRef) {
            $existingBooking = Booking::where('booking_ref_id', $existingBookingRef->id)
                ->where('booking_type', 'open-group')
                ->first();
            if ($existingBooking) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => true, 'booking_id' => $existingBooking->id]);
                }
                return redirect()->route('admin.closed-groups.book')->with('success', 'Closed group booking created. REF: ' . $existingBooking->id);
            }
        }

        $bookingItinerary = $groupItinerary;
        $selectedRoomDays = [];
        foreach ((array) ($data['selected_rooms'] ?? []) as $selectedRoom) {
            $dayIndex = (int) $selectedRoom['day_index'];
            if (!isset($bookingItinerary[$dayIndex]) || !is_array($bookingItinerary[$dayIndex])) {
                $bookingItinerary[$dayIndex] = [];
            }
            if (!isset($selectedRoomDays[$dayIndex])) {
                $bookingItinerary[$dayIndex]['rooms'] = [];
                $selectedRoomDays[$dayIndex] = true;
            }
            $bookingItinerary[$dayIndex]['rooms'][] = (int) $selectedRoom['room_id'];
            $bookingItinerary[$dayIndex]['accommodation'] = (int) $selectedRoom['accommodation_id'];
        }

        foreach ($bookingItinerary as $dayIndex => $dayEntry) {
            if (is_array($dayEntry) && !empty($dayEntry['accommodation']) && empty($dayEntry['rooms'])) {
                $bookingItinerary[$dayIndex]['rooms'] = [-1];
            }
        }

        foreach ((array) ($data['guest_assignments'] ?? []) as $key => $assigned) {
            $parts = explode('_', (string) $key, 2);
            if (count($parts) !== 2 || !ctype_digit($parts[0])) {
                continue;
            }
            $dayIndex = (int) $parts[0];
            if (!isset($bookingItinerary[$dayIndex]) || !is_array($bookingItinerary[$dayIndex])) {
                continue;
            }
            $bookingItinerary[$dayIndex]['guest_assignments'][$key] = is_array($assigned)
                ? $assigned
                : explode(',', (string) $assigned);
        }

        $pricingService = new PackagePricingService();
        $priceBreakdown = $this->calculateAdminGroupPriceBreakdown($group, $bookingItinerary, max(1, (int) $data['pax']));
        $computedTotal = (float) ($priceBreakdown['total'] ?? 0);

        DB::beginTransaction();
        try {
            Log::channel('closed_group')->info('Creating Trip record', ['group_id' => $group->id, 'title' => $group->name ?? null]);
            $groupStartDate = $this->resolveGroupDateValue($group, 'available_from');
            $groupEndDate = $this->resolveGroupDateValue($group, 'available_to');
            $leadFullName = trim(($data['lead_first_name'] ?? '') . ' ' . ($data['lead_last_name'] ?? ''));

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
                'booking_type' => 'open-group',
            ]);

            Log::channel('closed_group')->info('Booking created', ['booking_id' => $booking->id]);

            $packageLineItem = BookingLineItem::create([
                'booking_id' => $booking->id,
                'service_type' => 'package',
                'service_id' => $group->id,
                'quantity' => 1,
                'price' => $computedTotal,
                'start_date' => $groupStartDate,
                'end_date' => $groupEndDate,
                'status' => 'active',
            ]);
            $travellersByName = [];
            $leadTraveller = Traveller::create([
                'trip_id' => $trip->id,
                'name' => $leadFullName,
                'email' => $data['lead_email'] ?? null,
                'phone' => $data['lead_phone'] ?? null,
                'relationship' => 'lead',
            ]);
            $travellersByName[$leadFullName] = $leadTraveller;

            if (!empty($data['guests']) && is_array($data['guests'])) {
                foreach ($data['guests'] as $g) {
                    $full = trim(($g['first_name'] ?? '') . ' ' . ($g['last_name'] ?? ''));
                    if ($full !== '') {
                        $traveller = Traveller::create([
                            'trip_id' => $trip->id,
                            'name' => $full,
                            'email' => $g['email'] ?? null,
                            'phone' => $g['phone'] ?? null,
                            'date_of_birth' => $g['dob'] ?? null,
                            'relationship' => $g['relation'] ?? 'guest',
                        ]);
                        $travellersByName[$full] = $traveller;
                    }
                }
            }
            foreach ($travellersByName as $traveller) {
                BliTravellerAllocation::create([
                    'bli_id' => $packageLineItem->id,
                    'traveller_id' => $traveller->id,
                ]);
            }

            $bookingRef = BookingRef::create([
                'trip_id' => $trip->id,
                'booking_ref_code' => app(\App\Services\BookingReferenceGenerator::class)->generateCommon($trip->id),
                'idempotency_key' => $idempotencyKey,
                'total_amount' => $computedTotal,
            ]);
            $booking->booking_ref_id = $bookingRef->id;
            $booking->save();

            $itinerary = $bookingItinerary;
            // pricing service for computing authoritative per-service amounts
            $pricingService = new PackagePricingService();
            $travelerAccount = !empty($data['lead_email'])
                ? \App\Models\TravelerAccount::where('email', $data['lead_email'])->first()
                : null;
            $pricingAmounts = [];
            foreach ($priceBreakdown['items'] ?? [] as $priceItem) {
                $pricingAmounts[(int) ($priceItem['day'] ?? 0) . '|' . strtolower((string) ($priceItem['type'] ?? ''))] = (float) ($priceItem['amount'] ?? 0);
            }
            $pricingDays = [];
            $pricingDayNumber = 0;
            foreach ($itinerary as $pricingDayIndex => $pricingDayEntry) {
                if (!is_array($pricingDayEntry) || !is_numeric($pricingDayIndex) || (int) $pricingDayIndex < 0) {
                    continue;
                }
                if ((int) ($group->no_of_days ?? 0) > 0 && (int) $pricingDayIndex + 1 > (int) $group->no_of_days) {
                    continue;
                }
                if ($pricingService->isMeaningfulDayEntry($pricingDayEntry)) {
                    $pricingDays[$pricingDayIndex] = ++$pricingDayNumber;
                }
            }

            $postedAssignments = $request->input('guest_assignments', []);

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
            foreach ($itinerary as $dayIndex => $dayEntry) {
                if (!is_numeric($dayIndex) || (int) $dayIndex < 0) {
                    continue;
                }
                if ((int) ($group->no_of_days ?? 0) > 0 && (int) $dayIndex + 1 > (int) $group->no_of_days) {
                    continue;
                }
                if (!is_array($dayEntry) || !$pricingService->isMeaningfulDayEntry($dayEntry)) {
                    continue;
                }
                Log::channel('closed_group')->info('Processing itinerary day', ['day_index' => $dayIndex, 'day_entry' => $dayEntry]);

                $dayDate = $groupStartDate ? Carbon::parse($groupStartDate)->addDays((int) $dayIndex) : null;

                if (!empty($dayEntry['accommodation'])) {
                    $accommodationId = (int) $dayEntry['accommodation'];
                    $selectedRoomIds = array_values(array_filter(array_map('intval', (array) ($dayEntry['rooms'] ?? []))));
                    $accommodationDayAmount = $pricingAmounts[($pricingDays[$dayIndex] ?? 0) . '|accommodation'] ?? 0.0;
                    $roomAmountTotal = 0.0;
                    $roomAmounts = [];
                    foreach ($selectedRoomIds as $roomId) {
                        $roomEntry = $dayEntry;
                        $roomEntry['rooms'] = [$roomId];
                        $roomAmounts[$roomId] = $pricingService->getGroupAccommodationAmount(
                            \App\Models\Accommodation::find($accommodationId),
                            $roomEntry,
                            $group,
                            max(1, (int) $data['pax']),
                            0,
                            0
                        );
                        $roomAmountTotal += $roomAmounts[$roomId];
                    }

                    if (!empty($roomAmounts) && abs($roomAmountTotal - $accommodationDayAmount) > 0.01) {
                        $scale = $roomAmountTotal > 0 ? $accommodationDayAmount / $roomAmountTotal : 0;
                        foreach ($roomAmounts as $roomId => $amount) {
                            $roomAmounts[$roomId] = round($amount * $scale, 2);
                        }
                    }

                    foreach ($selectedRoomIds as $roomId) {
                        Log::channel('closed_group')->info('Creating AccommodationBooking attempt', ['trip_id' => $trip->id, 'room_id' => $roomId, 'accommodation_id' => $accommodationId]);
                        $room = AccommodationRoom::find($roomId);
                        if (!$room) {
                            Log::channel('closed_group')->error('AccommodationRoom not found', ['room_id' => $roomId, 'accommodation_id' => $accommodationId]);
                            continue;
                        }
                        $checkIn = $dayDate ? $dayDate->toDateString() : null;
                        $checkOut = $checkIn ? Carbon::parse($checkIn)->addDay()->toDateString() : null;

                        $accAmount = (float) ($roomAmounts[$roomId] ?? 0);

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
                            'traveler_account_id' => $travelerAccount?->id,
                            'traveler_relation' => 'self',
                            'traveler_first_name' => $data['lead_first_name'] ?? null,
                            'traveler_last_name' => $data['lead_last_name'] ?? null,
                            'guest_email' => $data['lead_email'] ?? null,
                            'check_in_date' => $checkIn,
                            'check_out_date' => $checkOut,
                            'rooms_booked' => 1,
                            'adults' => (int) $adultsCount,
                            'children' => 0,
                            'booking_status' => 'Pending',
                            'total_amount' => (float) round($accAmount, 2),
                            'currency' => 'USD',
                            'source_channel' => 'Package',
                            'payment_method' => 'Bank Transfer',
                            'booking_ref_id' => $bookingRef->id,
                            'booked_at' => now(),
                            'trip_id' => $trip->id,
                        ];
                        $acc = AccommodationBooking::create($accData);
                        Log::channel('closed_group')->info('AccommodationBooking created', ['id' => $acc->id ?? null, 'room_id' => $roomId]);
                    }
                }

                if (!empty($dayEntry['activity'])) {
                    $activity = Activity::with('schedulingTimeSlots')->find((int) $dayEntry['activity']);
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

                        $activityTimeSlotId = null;
                        $requiredParticipants = max(1, (int) $data['pax']);
                        foreach ($activity->schedulingTimeSlots ?? [] as $slot) {
                            $capacity = (int) ($slot->capacity_per_slot ?? 0);
                            if ($capacity <= 0) {
                                $activityTimeSlotId = $slot->timeslot_id;
                                break;
                            }
                            $occupied = ActivityBooking::where('activity_id', $activity->id)
                                ->where('activity_time_slot_id', $slot->timeslot_id)
                                ->whereDate('activity_date', $dayDate?->toDateString() ?? now()->toDateString())
                                ->get()
                                ->sum(fn ($existing) => (int) ($existing->adults ?? 0) + (int) ($existing->children ?? 0));
                            if ($occupied + $requiredParticipants <= $capacity) {
                                $activityTimeSlotId = $slot->timeslot_id;
                                break;
                            }
                        }

                        // Compute activity amount using canonical pricing
                        $activityAmount = (float) ($pricingAmounts[($pricingDays[$dayIndex] ?? 0) . '|activity'] ?? 0);

                        $abData = [
                            'booking_reference' => $this->generateBookingRef('activity', $trip->id, $dayDate?->toDateString() ?? now()->toDateString()),
                            'activity_id' => $activity->id,
                            'variant_id' => $variantId,
                            'variant_name' => $variantName,
                            'guest_name' => $leadFullName,
                            'traveler_account_id' => $travelerAccount?->id,
                            'traveler_relation' => 'self',
                            'traveler_first_name' => $data['lead_first_name'] ?? null,
                            'traveler_last_name' => $data['lead_last_name'] ?? null,
                            'guest_email' => $data['lead_email'] ?? null,
                            'guest_phone' => $data['lead_phone'] ?? null,
                            'activity_date' => $dayDate?->toDateString() ?? now()->toDateString(),
                            'adults' => max(1, (int) $data['pax']),
                            'children' => 0,
                            'booking_status' => 'Pending',
                            'total_amount' => (float) round($activityAmount, 2),
                            'currency' => 'USD',
                            'payment_method' => 'Bank Transfer',
                            'source_channel' => 'Package',
                            'booking_ref_id' => $bookingRef->id,
                            'special_requests' => null,
                            'activity_time_slot_id' => $activityTimeSlotId,
                            'booked_at' => now(),
                            'trip_id' => $trip->id,
                        ];
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

                        $routeLegCandidates = [];
                        if (!empty($selectedGroups)) {
                            foreach ($selectedGroups as $groupIndex => $groupMeta) {
                                foreach ($groupMeta['legs'] ?? [] as $leg) {
                                    $routeId = (string) ($leg['route_id'] ?? '');
                                    $normalizedRouteId = $this->normalizeTransportRouteKey($routeId);
                                    $route = $transport->routes->first(function ($candidate) use ($routeId, $normalizedRouteId) {
                                        return (string) ($candidate->id ?? '') === $routeId
                                            || (string) ($candidate->route_id ?? '') === $routeId
                                            || $this->normalizeTransportRouteKey((string) ($candidate->route_id ?? '')) === $normalizedRouteId;
                                    });
                                    if (!$route && $routeId !== '') {
                                        $route = \App\Models\TransportRoute::where('transport_id', $transport->id)
                                            ->where(function ($query) use ($routeId, $normalizedRouteId) {
                                                $query->where('route_id', $routeId)
                                                    ->orWhere('route_id', $normalizedRouteId)
                                                    ->orWhere('id', is_numeric($routeId) ? (int) $routeId : 0);
                                            })
                                            ->first();
                                    }
                                    if ($route) {
                                        $routeLegCandidates[] = [
                                            'route' => $route,
                                            'leg' => $leg,
                                            'group_index' => $groupIndex,
                                        ];
                                    }
                                }
                            }
                        }

                        if (!empty($selectedGroups) && $routeLegCandidates === []) {
                            throw new \RuntimeException('A selected group transport route could not be resolved.');
                        }

                        $transportDayAmount = (float) ($pricingAmounts[($pricingDays[$dayIndex] ?? 0) . '|transport'] ?? 0);
                        $routeAmountCandidates = [];
                        $candidateAmountTotal = 0.0;
                        foreach ($routeLegCandidates as $routeEntry) {
                            $route = $routeEntry['route'] ?? null;
                            if (!$route) {
                                Log::channel('closed_group')->warning('Transport route not found for selected id', ['route_entry' => $routeEntry]);
                                continue;
                            }
                            $leg = $routeEntry['leg'];
                            $reverse = ($leg['direction'] ?? 'forward') === 'reverse';
                            $candidateAmount = $pricingService->getTransportRouteLegAmount(
                                $transport,
                                $route,
                                $group,
                                $reverse,
                                !empty($leg['is_return_pair']),
                                $dayDate?->toDateString()
                            );
                            $routeAmountCandidates[] = $routeEntry + ['candidate_amount' => $candidateAmount];
                            $candidateAmountTotal += $candidateAmount;
                        }

                        $routeAmountScale = $candidateAmountTotal > 0
                            ? $transportDayAmount / $candidateAmountTotal
                            : 0;
                        $allocatedTransportAmount = 0.0;
                        $returnGroupReferences = [];
                        $lastCandidateIndex = count($routeAmountCandidates) - 1;
                        foreach ($routeAmountCandidates as $candidateIndex => $routeEntry) {
                            $route = $routeEntry['route'];
                            $leg = $routeEntry['leg'];
                            $reverse = ($leg['direction'] ?? 'forward') === 'reverse';
                            $routeFrom = $reverse
                                ? ($route->route_to ?? $route->dropoff_value ?? '')
                                : ($route->route_from ?? $route->pickup_value ?? '');
                            $routeTo = $reverse
                                ? ($route->route_from ?? $route->pickup_value ?? '')
                                : ($route->route_to ?? $route->dropoff_value ?? '');
                            $pickupDate = $dayDate?->toDateString() ?? now()->toDateString();
                            $routeAmount = $candidateIndex === $lastCandidateIndex
                                ? round($transportDayAmount - $allocatedTransportAmount, 2)
                                : round((float) $routeEntry['candidate_amount'] * $routeAmountScale, 2);
                            $allocatedTransportAmount += $routeAmount;
                            $bookingReference = $this->generateBookingRef('transport', $trip->id, $pickupDate);
                            $groupIndex = (string) $routeEntry['group_index'];
                            $transportGroupReference = !empty($leg['is_return_pair'])
                                ? ($returnGroupReferences[$groupIndex] ??= $bookingReference . '-GROUP')
                                : null;

                            $tbData = [
                                'transport_id' => $transport->id,
                                'traveler_account_id' => $travelerAccount?->id,
                                'booking_reference' => $bookingReference,
                                'trip_type' => !empty($leg['is_return_pair']) ? ($reverse ? 'RETURN' : 'OUTBOUND') : 'ONE_WAY',
                                'transport_group_reference' => $transportGroupReference,
                                'guest_name' => $leadFullName,
                                'guest_email' => $data['lead_email'] ?? '',
                                'guest_phone' => $data['lead_phone'] ?? null,
                                'route_from' => $routeFrom,
                                'route_to' => $routeTo,
                                'pickup_date' => $pickupDate,
                                'pickup_time' => $leg['pickup_time'] ?? null,
                                'return_date' => null,
                                'return_time' => null,
                                'pickup_address' => $reverse ? ($dayEntry['dropoff_address'] ?? null) : ($dayEntry['pickup_address'] ?? null),
                                'dropoff_address' => $reverse ? ($dayEntry['pickup_address'] ?? null) : ($dayEntry['dropoff_address'] ?? null),
                                'service_type' => $dayEntry['service_type'] ?? null,
                                'adults' => max(1, (int) $data['pax']),
                                'children' => 0,
                                'total_passengers' => max(1, (int) $data['pax']),
                                'transport_price' => $routeAmount,
                                'traveler_first_name' => $data['lead_first_name'] ?? null,
                                'traveler_middle_name' => null,
                                'traveler_last_name' => $data['lead_last_name'] ?? null,
                                'traveler_relation' => 'self',
                                'price_per_person' => 0.0,
                                'total_amount' => (float) round($routeAmount, 2),
                                'currency' => 'USD',
                                'booking_status' => TransportBooking::STATUS_PENDING,
                                'payment_method' => 'Bank Transfer',
                                'source_channel' => 'Package',
                                'booking_ref_id' => $bookingRef->id,
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
        $s = preg_replace('/-(fwd|rev)$/i', '', $s);
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

                    $resolvedRouteId = $routeId ?? $baseKey;
                    $resolvedRouteId = trim((string) $resolvedRouteId);
                    $normalizedRouteId = $this->normalizeTransportRouteKey($resolvedRouteId);
                    if ($normalizedRouteId === '') continue;

                    if (!isset($groups[$baseKey])) {
                        $groups[$baseKey] = ['route_ids' => [], 'directions' => [], 'legs' => []];
                    }
                    if (!in_array($normalizedRouteId, $groups[$baseKey]['route_ids'], true)) {
                        $groups[$baseKey]['route_ids'][] = $normalizedRouteId;
                    }

                    $direction = is_string($routeKey) && preg_match('/-rev$/i', $routeKey) ? 'reverse' : 'forward';
                    $startHour = trim((string) ($routeData['start_hour'] ?? ''));
                    $startMinute = trim((string) ($routeData['start_min'] ?? ''));
                    $pickupTime = preg_match('/^\d{1,2}$/', $startHour)
                        && preg_match('/^\d{1,2}$/', $startMinute)
                        && (int) $startHour <= 23
                        && (int) $startMinute <= 59
                        ? sprintf('%02d:%02d:00', (int) $startHour, (int) $startMinute)
                        : null;
                    $legKey = $normalizedRouteId . '|' . $direction;
                    $existingLegKeys = array_map(fn ($leg) => $leg['route_id'] . '|' . $leg['direction'], $groups[$baseKey]['legs']);
                    if (!in_array($legKey, $existingLegKeys, true)) {
                        $groups[$baseKey]['legs'][] = [
                            'route_id' => $normalizedRouteId,
                            'direction' => $direction,
                            'pickup_time' => $pickupTime,
                        ];
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
                        $groups[$baseKey] = ['route_ids' => [], 'directions' => [], 'legs' => []];
                    }
                    if (!in_array($normalized, $groups[$baseKey]['route_ids'], true)) {
                        $groups[$baseKey]['route_ids'][] = $normalized;
                        $groups[$baseKey]['legs'][] = ['route_id' => $normalized, 'direction' => 'forward', 'pickup_time' => null];
                    }
                }
            }
        }

        foreach ($groups as $baseKey => $meta) {
            $hasReturnPair = in_array('fwd', $meta['directions'] ?? [], true)
                && in_array('rev', $meta['directions'] ?? [], true);
            foreach ($groups[$baseKey]['legs'] as &$leg) {
                $leg['is_return_pair'] = $hasReturnPair;
            }
            unset($leg);
        }

        return array_values($groups);
    }
}
