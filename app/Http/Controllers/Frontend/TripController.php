<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SavedGuest;
use App\Models\Trip;
use App\Models\ActivityBooking;
use App\Models\AccommodationBooking;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index()
    {
        $traveler = auth('traveler')->user();
        $trips = Trip::where('traveler_account_id', $traveler->id)
            ->with(['accommodationBookings', 'activityBookings'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('frontend.traveler.trips', compact('trips'))->with('guestMode', false);
    }

    public function show(Trip $trip)
    {
        $traveler = auth('traveler')->user();
        if ($trip->traveler_account_id !== $traveler->id) {
            abort(403);
        }
        $trip->load('bookings.lineItems.travellers', 'travellers');
        
        // Load associated accommodation and activity bookings (exclude guest bookings)
        $accommodationBookings = \App\Models\AccommodationBooking::where('trip_id', $trip->id)
            ->where('is_guest', 0)
            ->with(['accommodation', 'room', 'guests'])
            ->orderBy('check_in_date', 'asc')
            ->get();
        
        $activityBookings = \App\Models\ActivityBooking::where('trip_id', $trip->id)
            ->where('is_guest', 0)
            ->with(['activity', 'guests'])
            ->orderBy('activity_date', 'asc')
            ->get();
        
        // Calculate actual trip dates from all bookings
        $allDates = [];
        foreach ($accommodationBookings as $booking) {
            if ($booking->check_in_date) $allDates[] = $booking->check_in_date;
            if ($booking->check_out_date) $allDates[] = $booking->check_out_date;
        }
        foreach ($activityBookings as $booking) {
            if ($booking->activity_date) $allDates[] = $booking->activity_date;
        }
        
        $tripStartDate = !empty($allDates) ? min($allDates) : $trip->start_date;
        $tripEndDate = !empty($allDates) ? max($allDates) : $trip->end_date;
        
        return view('frontend.traveler.trip-detail', compact('trip', 'accommodationBookings', 'activityBookings', 'tripStartDate', 'tripEndDate'));
    }

    public function manageGuests(Trip $trip, $bookingId)
    {
        $traveler = auth('traveler')->user();
        if ($trip->traveler_account_id !== $traveler->id) {
            abort(403);
        }

        // Find the booking
        $booking = \App\Models\AccommodationBooking::where('id', $bookingId)->where('trip_id', $trip->id)->with('guests')->first();
        if (!$booking) {
            $booking = \App\Models\ActivityBooking::where('id', $bookingId)->where('trip_id', $trip->id)->with('guests')->first();
        }
        if (!$booking) {
            abort(404);
        }

        if ($booking instanceof \App\Models\ActivityBooking) {
            $booking->load(['activity.operator', 'activity.operationsStaffing', 'activity.schedulingTimeSlots']);
        }

        $savedGuests = SavedGuest::where('user_id', $traveler->id)->get();

        $selfGuest = new SavedGuest([
            'first_name' => $traveler->profile->first_name ?? $traveler->first_name ?? '',
            'middle_name' => $traveler->profile->middle_name ?? '',
            'last_name' => $traveler->profile->last_name ?? $traveler->last_name ?? '',
            'dob' => optional($traveler->profile->date_of_birth)->format('Y-m-d'),
            'gender' => $traveler->profile->gender ?? null,
            'nationality' => $traveler->profile->nationality ?? null,
            'passport_number' => $traveler->profile->passport_number ?? null,
            'notes' => null,
        ]);
        $selfGuest->id = 'self';
        $selfGuest->relation = 'self';
        $savedGuests->prepend($selfGuest);

        $countries = [
            'Australia',
            'Canada',
            'China',
            'France',
            'Germany',
            'India',
            'Italy',
            'Kenya',
            'Madagascar',
            'Mauritius',
            'Reunion',
            'Singapore',
            'South Africa',
            'United Arab Emirates',
            'United Kingdom',
            'United States',
        ];

        $activityTimeSlots = [];
        if ($booking instanceof \App\Models\ActivityBooking && $booking->activity) {
            $activityTimeSlots = $booking->activity->schedulingTimeSlots ?? collect();
        }

        return view('frontend.traveler.manage-guests', compact('trip', 'booking', 'savedGuests', 'countries', 'activityTimeSlots'));
    }

    public function downloadVoucher(Trip $trip, $bookingId, $guestId = null)
    {
        $traveler = auth('traveler')->user();
        if ($trip->traveler_account_id !== $traveler->id) {
            abort(403);
        }

        // Find the booking (accommodation or activity)
        $booking = \App\Models\AccommodationBooking::where('id', $bookingId)
            ->where('trip_id', $trip->id)
            ->with(['accommodation', 'room', 'guests'])
            ->first();

        if (!$booking) {
            $booking = \App\Models\ActivityBooking::where('id', $bookingId)
                ->where('trip_id', $trip->id)
                ->with(['activity.operator', 'activity.operationsStaffing', 'activity.schedulingTimeSlots', 'guests'])
                ->first();
        }

        if (!$booking) {
            abort(404);
        }

        $isActivity = $booking instanceof \App\Models\ActivityBooking;
        $isAccommodation = $booking instanceof \App\Models\AccommodationBooking;

        if ($isActivity) {
            $activity = $booking->activity;
            if (!$activity) {
                abort(404);
            }
            $operator = $activity->operator;
            $ops = $activity->operationsStaffing;
        } elseif ($isAccommodation) {
            $accommodation = $booking->accommodation;
            if (!$accommodation) {
                abort(404);
            }
            $room = $booking->room;
            $operator = $accommodation->operator;
        } else {
            abort(404);
        }
        $voucherDate = $isActivity ? optional($booking->activity_date)->format('d/m/Y') : (optional($booking->check_in_date)->format('d/m/Y') . ' - ' . optional($booking->check_out_date)->format('d/m/Y'));
        $serviceName = $isActivity ? ($activity->activity_name ?? 'Activity') : ($accommodation->property_name ?? 'Accommodation');
        $variantName = $isActivity ? ($booking->variant_name ? 'Variant: ' . $booking->variant_name : 'Standard option') : ($room ? 'Room: ' . $room->room_name : 'Standard room');
        $duration = $isActivity ? ($activity->duration ? 'Duration: ' . $activity->duration : '') : '';
        $allowedTags = '<strong><em><u><br><p><ul><ol><li><b><i>';
        if ($isActivity) {
            $meetingPoint = $activity->meeting_point_details ? strip_tags($activity->meeting_point_details, $allowedTags) : 'Not available';
            $overview = $activity->overview ? strip_tags($activity->overview, $allowedTags) : 'Not available';
            if ($meetingPoint !== 'Not available') {
                $meetingPoint = preg_replace('/\\r\\n|\\r|\\n/', '<br>', $meetingPoint);
            }
            if ($overview !== 'Not available') {
                $overview = preg_replace('/\\r\\n|\\r|\\n/', '<br>', $overview);
            }
        } elseif ($isAccommodation) {
            $meetingPoint = 'Check-in: ' . optional($booking->check_in_date)->format('d/m/Y') . '<br>Check-out: ' . optional($booking->check_out_date)->format('d/m/Y');
            $overview = $accommodation->property_description ? strip_tags($accommodation->property_description, $allowedTags) : 'Not available';
            if ($overview !== 'Not available') {
                $overview = preg_replace('/\\r\\n|\\r|\\n/', '<br>', $overview);
            }
        }

        if ($isActivity) {
            $reservationContact = [];
            if ($activity->reservation_contact_name) {
                $reservationContact[] = 'Name: ' . $activity->reservation_contact_name;
            }
            if ($activity->reservation_contact_email) {
                $reservationContact[] = 'Email: ' . $activity->reservation_contact_email;
            }
            if ($activity->reservation_contact_phone) {
                $reservationContact[] = 'Phone: ' . $activity->reservation_contact_phone;
            }
            if ($activity->reservation_contact_mobile) {
                $reservationContact[] = 'Mobile: ' . $activity->reservation_contact_mobile;
            }

            $accountingContact = [];
            if ($activity->accounting_contact_name) {
                $accountingContact[] = 'Name: ' . $activity->accounting_contact_name;
            }
            if ($activity->accounting_contact_email) {
                $accountingContact[] = 'Email: ' . $activity->accounting_contact_email;
            }
            if ($activity->accounting_contact_phone) {
                $accountingContact[] = 'Phone: ' . $activity->accounting_contact_phone;
            }
            if ($activity->accounting_contact_mobile) {
                $accountingContact[] = 'Mobile: ' . $activity->accounting_contact_mobile;
            }

            $managementContact = [];
            if ($activity->management_contact_name) {
                $managementContact[] = 'Name: ' . $activity->management_contact_name;
            }
            if ($activity->management_contact_email) {
                $managementContact[] = 'Email: ' . $activity->management_contact_email;
            }
            if ($activity->management_contact_phone) {
                $managementContact[] = 'Phone: ' . $activity->management_contact_phone;
            }
            if ($activity->management_contact_mobile) {
                $managementContact[] = 'Mobile: ' . $activity->management_contact_mobile;
            }

            $opsContact = [];
            if ($ops?->ops_contact_name) {
                $opsContact[] = 'Name: ' . $ops->ops_contact_name;
            }
            if ($ops?->ops_contact_mobile) {
                $opsContact[] = 'Mobile: ' . $ops->ops_contact_mobile;
            }
        } elseif ($isAccommodation) {
            $reservationContact = [];
            if ($accommodation->reservation_contact_name) {
                $reservationContact[] = 'Name: ' . $accommodation->reservation_contact_name;
            }
            if ($accommodation->reservation_contact_email) {
                $reservationContact[] = 'Email: ' . $accommodation->reservation_contact_email;
            }
            if ($accommodation->reservation_contact_phone) {
                $reservationContact[] = 'Phone: ' . $accommodation->reservation_contact_phone;
            }
            if ($accommodation->reservation_contact_mobile) {
                $reservationContact[] = 'Mobile: ' . $accommodation->reservation_contact_mobile;
            }

            $accountingContact = [];
            if ($accommodation->accounting_contact_name) {
                $accountingContact[] = 'Name: ' . $accommodation->accounting_contact_name;
            }
            if ($accommodation->accounting_contact_email) {
                $accountingContact[] = 'Email: ' . $accommodation->accounting_contact_email;
            }
            if ($accommodation->accounting_contact_phone) {
                $accountingContact[] = 'Phone: ' . $accommodation->accounting_contact_phone;
            }
            if ($accommodation->accounting_contact_mobile) {
                $accountingContact[] = 'Mobile: ' . $accommodation->accounting_contact_mobile;
            }

            $managementContact = [];
            if ($accommodation->management_contact_name) {
                $managementContact[] = 'Name: ' . $accommodation->management_contact_name;
            }
            if ($accommodation->management_contact_email) {
                $managementContact[] = 'Email: ' . $accommodation->management_contact_email;
            }
            if ($accommodation->management_contact_phone) {
                $managementContact[] = 'Phone: ' . $accommodation->management_contact_phone;
            }
            if ($accommodation->management_contact_mobile) {
                $managementContact[] = 'Mobile: ' . $accommodation->management_contact_mobile;
            }

            $opsContact = []; // No ops for accommodation
        }

        $voucherGuest = null;
        if ($guestId) {
            $voucherGuest = $booking->guests->firstWhere('id', $guestId);
            if (!$voucherGuest) {
                abort(404);
            }
        }

        $guestsForVoucher = $voucherGuest ? [$voucherGuest] : $booking->guests->all();
        
        // For activity bookings, check if time slots are required and present
        if ($isActivity && !$voucherGuest) {
            $participantTimeSlots = $booking->participant_time_slots ?? [];
            $missingTimeSlots = [];
            foreach ($guestsForVoucher as $index => $guest) {
                if (empty($participantTimeSlots[$guest->guest_number ?? ($index + 1)])) {
                    $missingTimeSlots[] = trim($guest->first_name . ' ' . ($guest->last_name ?? ''));
                }
            }
            if (!empty($missingTimeSlots)) {
                return redirect()->back()->with('error', 'Time slots must be selected for all participants before downloading the voucher. Missing time slots for: ' . implode(', ', $missingTimeSlots));
            }
        }
        
        // Get activity time slot info for booking details
        $activityTimeSlotDisplay = '-';
        if ($isActivity && !empty($guestsForVoucher)) {
            $firstGuest = $guestsForVoucher[0];
            if (isset($booking->participant_time_slots[$firstGuest->guest_number ?? 1])) {
                $timeSlotId = $booking->participant_time_slots[$firstGuest->guest_number ?? 1];
                $timeSlot = $booking->activity->schedulingTimeSlots->where('timeslot_id', $timeSlotId)->first();
                if ($timeSlot) {
                    $activityTimeSlotDisplay = e($timeSlot->start_time . ' - ' . $timeSlot->end_time);
                }
            }
        }
        
        $guestRows = '';
        foreach ($guestsForVoucher as $index => $guest) {
            $guestRows .= '<tr>' .
                '<td>' . e(trim($guest->first_name . ' ' . ($guest->last_name ?? ''))) . '</td>' .
                '<td>' . e($guest->nationality ?? '-') . '</td>' .
                '<td>' . ($guest->dob ? e(optional($guest->dob)->format('d/m/Y')) : '-') . '</td>' .
                '</tr>';
        }

        $html = '<h1 style="font-size:18px;">' . e($isActivity ? 'Activity' : 'Accommodation') . ' Voucher</h1>' .
            '<h2 style="font-size:14px; margin-top:12px; margin-bottom:12px;">Booking Details</h2>' .
            '<table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse:collapse;">' .
            '<tr style="background-color:#f0f0f0;"><td width="30%" style="font-weight:bold;">Booking Reference:</td><td>' . e($booking->booking_reference) . '</td></tr>' .
            '<tr><td style="font-weight:bold;">Trip:</td><td>' . e($trip->id ? '100'.$trip->id : 'N/A') . '</td></tr>' .
            '<tr style="background-color:#f0f0f0;"><td style="font-weight:bold;">' . e($isActivity ? 'Activity' : 'Accommodation') . ':</td><td>' . e($serviceName) . '</td></tr>' .
            '<tr><td style="font-weight:bold;">Date:</td><td>' . e($voucherDate) . '</td></tr>' .
            '<tr style="background-color:#f0f0f0;"><td style="font-weight:bold;">Variant:</td><td>' . e($variantName) . '</td></tr>' .
            '<tr><td style="font-weight:bold;">Duration:</td><td>' . ($duration ? e($duration) : 'N/A') . '</td></tr>' .
            ($isActivity ? '<tr style="background-color:#f0f0f0;"><td style="font-weight:bold;">Activity Time Slot:</td><td>' . $activityTimeSlotDisplay . '</td></tr>' : '') .
            '</table>' .
            '<h2 style="font-size:16px; margin-top:18px;">' . e($isActivity ? 'Activity' : 'Accommodation') . ' Details</h2>' .
            '<p>' . $overview . '</p>' .
            '<p><strong>' . e($isActivity ? 'Meeting Point' : 'Check-in/out Details') . ':</strong><br>' . $meetingPoint . '</p>' .
            '<h2 style="font-size:16px; margin-top:18px;">Participant Details</h2>' .
            '<table border="1" cellpadding="6" cellspacing="0" width="100%" style="border-collapse:collapse;">' .
            '<thead><tr style="background-color:#f5f5f5;"><th>Name</th><th>Nationality</th><th>Date of Birth</th></tr></thead>' .
            '<tbody>' . $guestRows . '</tbody></table>' .
            '<h2 style="font-size:16px; margin-top:18px;">Contacts</h2>' .
            '<p><strong>Reservation Contact</strong><br>' . (count($reservationContact) ? implode('<br>', array_map('e', $reservationContact)) : 'Not available') . '</p>' .
            '<p><strong>Accounting Contact</strong><br>' . (count($accountingContact) ? implode('<br>', array_map('e', $accountingContact)) : 'Not available') . '</p>' .
            '<p><strong>Management Contact</strong><br>' . (count($managementContact) ? implode('<br>', array_map('e', $managementContact)) : 'Not available') . '</p>' .
            ($isActivity ? '<p><strong>Operations Contact</strong><br>' . (count($opsContact) ? implode('<br>', array_map('e', $opsContact)) : 'Not available') . '</p>' : '');

        $pdf = new \TCPDF();
        $pdf->SetCreator('Holidaysio');
        $pdf->SetAuthor($traveler->name ?? $traveler->first_name ?? 'Traveler');
        $pdf->SetTitle('Voucher - ' . ($booking->booking_reference ?? ($isActivity ? 'activity' : 'accommodation')));
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 11);
        $pdf->writeHTML($html, true, false, true, false, '');
        $filename = ($isActivity ? 'activity' : 'accommodation') . '-voucher-' . preg_replace('/[^A-Za-z0-9_-]/', '', $booking->booking_reference) . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    public function updateGuests(Request $request, Trip $trip, $bookingId)
    {
        $traveler = auth('traveler')->user();
        if ($trip->traveler_account_id !== $traveler->id) {
            abort(403);
        }

        // Find the booking
        $booking = \App\Models\AccommodationBooking::where('id', $bookingId)->where('trip_id', $trip->id)->first();
        if (!$booking) {
            $booking = \App\Models\ActivityBooking::where('id', $bookingId)->where('trip_id', $trip->id)->first();
        }
        if (!$booking) {
            abort(404);
        }

        // Validate and update guests
        $request->validate([
            'guests' => 'array',
            'guests.*.first_name' => 'required|string|max:255',
            'guests.*.last_name' => 'required|string|max:255',
            'guests.*.dob' => 'required|date',
            'guests.*.gender' => 'nullable|string',
            'guests.*.nationality' => 'required|string',
            'guests.*.passport_number' => 'nullable|string',
            'guests.*.notes' => 'nullable|string',
            'guests.*.time_slot' => ($booking instanceof \App\Models\ActivityBooking ? 'required|string' : 'nullable|string'),
        ]);

        // Delete existing guests
        $booking->guests()->delete();

        $guestInput = $request->input('guests', []);
        if (!is_array($guestInput)) {
            $guestInput = [];
        }

        // Gender mapping from various formats to enum values
        $genderMapping = [
            'Mr' => 'male',
            'Mrs' => 'female',
            'Ms' => 'female',
            'Male' => 'male',
            'Female' => 'female',
            'Non-binary' => 'non_binary',
            'Other' => 'other',
        ];

        // Add new guests with explicit guest_number ordering
        foreach (array_values($guestInput) as $index => $guestData) {
            $guestData['guest_number'] = $index + 1;
            $guestData['gender'] = $genderMapping[$guestData['gender']] ?? $guestData['gender'];
            $guestData['booking_type'] = $booking instanceof \App\Models\AccommodationBooking ? 'accommodation' : 'activity';
            $booking->guests()->create($guestData);
        }

        // For activity bookings, update the participant_time_slots in the booking
        if ($booking instanceof \App\Models\ActivityBooking) {
            $participantTimeSlots = [];
            foreach (array_values($guestInput) as $index => $guestData) {
                $participantTimeSlots[$index + 1] = $guestData['time_slot'] ?? '';
            }
            $booking->update(['participant_time_slots' => $participantTimeSlots]);
        }

        return redirect()->route('traveler.trip.booking.manage-guests', ['trip' => $trip->id, 'booking' => $booking->id])->with('success', 'Guests updated successfully.');
    }
}
