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

class ClosedGroupBookingController extends Controller
{
    public function create()
    {
        // show available closed groups to admin
        $groups = Group::orderBy('available_from', 'desc')->get();
        return view('admin.closed_groups.book', ['groups' => $groups]);
    }

    /**
     * Minimal booking reference generator for admin-created bookings
     */
    protected function generateBookingRef($type = 'misc', $tripId = null, $date = null)
    {
        $prefix = 'REF-' . strtoupper(substr($type, 0, 3));
        return $prefix . '-' . time() . '-' . rand(100, 999);
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'group_id' => 'required|integer|exists:groups,id',
            'lead_first_name' => 'required|string|max:255',
            'lead_last_name' => 'nullable|string|max:255',
            'lead_email' => 'nullable|email|max:255',
            'lead_phone' => 'nullable|string|max:50',
            'pax' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
            'guests' => 'nullable|array',
            'selected_rooms' => 'nullable|array',
            'guest_assignments' => 'nullable|array',
        ]);

        $group = Group::find($data['group_id']);

        DB::beginTransaction();
        try {
            // create Trip
            $trip = Trip::create([
                'traveler_account_id' => null,
                'title' => $group->name ?? 'Closed Group Booking',
                'start_date' => $group->available_from ?? null,
                'end_date' => $group->available_to ?? null,
                'status' => 'planned',
            ]);

            // create Booking
            $booking = Booking::create([
                'trip_id' => $trip->id,
                'operator_id' => null,
                'total_amount' => $data['total_amount'],
                'status' => 'pending',
            ]);

            // create BookingLineItem for group
            $bli = BookingLineItem::create([
                'booking_id' => $booking->id,
                'service_type' => 'group',
                'service_id' => $group->id,
                'quantity' => max(1, (int) $data['pax']),
                'price' => $data['total_amount'],
                'start_date' => $group->available_from ?? null,
                'end_date' => $group->available_to ?? null,
                'status' => 'active',
            ]);

            // create lead traveller
            $leadFullName = trim(($data['lead_first_name'] ?? '') . ' ' . ($data['lead_last_name'] ?? ''));
            $lead = Traveller::create([
                'trip_id' => $trip->id,
                'name' => $leadFullName,
                'email' => $data['lead_email'] ?? null,
                'phone' => $data['lead_phone'] ?? null,
                'relationship' => 'lead',
            ]);

            // create additional guest travellers if provided
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

            // Create accommodation bookings for selected rooms and assign guests
            if (!empty($data['selected_rooms']) && is_array($data['selected_rooms'])) {
                foreach ($data['selected_rooms'] as $sel) {
                    // expected each sel: { day_index, accommodation_id, room_id, check_in, check_out }
                    $accomBooking = \App\Models\AccommodationBooking::create([
                        'booking_reference' => $this->generateBookingRef('accommodation', $trip->id, $sel['check_in'] ?? now()->toDateString()),
                        'accommodation_id' => $sel['accommodation_id'] ?? null,
                        'room_id' => $sel['room_id'] ?? null,
                        'guest_name' => trim(($data['lead_first_name'] ?? '') . ' ' . ($data['lead_last_name'] ?? '')),
                        'traveler_account_id' => null,
                        'traveler_relation' => 'lead',
                        'guest_email' => $data['lead_email'] ?? null,
                        'check_in_date' => $sel['check_in'] ?? null,
                        'check_out_date' => $sel['check_out'] ?? null,
                        'rooms_booked' => 1,
                        'adults' => 1,
                        'children' => 0,
                        'booking_status' => 'Pending',
                        'total_amount' => 0,
                        'currency' => 'USD',
                        'source_channel' => 'Admin',
                        'booked_at' => now(),
                        'trip_id' => $trip->id,
                    ]);

                    // if guest assignments provided, create BookingGuest entries
                    $assignKey = ($sel['day_index'] ?? '') . '_' . ($sel['room_id'] ?? '');
                    $assignments = $data['guest_assignments'][$assignKey] ?? [];
                    if (!empty($assignments) && is_array($assignments)) {
                        foreach ($assignments as $guestIndex) {
                            $g = $data['guests'][$guestIndex] ?? null;
                            if ($g) {
                                \App\Models\BookingGuest::create([
                                    'booking_id' => $accomBooking->id,
                                    'booking_type' => 'accommodation',
                                    'guest_number' => (int) $guestIndex + 1,
                                    'relation' => $g['relation'] ?? 'guest',
                                    'first_name' => $g['first_name'] ?? null,
                                    'middle_name' => $g['middle_name'] ?? null,
                                    'last_name' => $g['last_name'] ?? null,
                                    'dob' => $g['dob'] ?? null,
                                    'gender' => $g['gender'] ?? null,
                                    'nationality' => $g['nationality'] ?? null,
                                    'passport_number' => $g['passport_number'] ?? null,
                                    'notes' => $g['notes'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.closed-groups.book')->with('success', 'Closed group booking created. REF: ' . ($booking->id ?? ''));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Admin closed group booking create failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create booking: ' . $e->getMessage());
        }
    }
}
