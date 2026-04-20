<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SavedGuest;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index()
    {
        $traveler = auth('traveler')->user();
        $trips = Trip::where('traveler_account_id', $traveler->id)->with('bookings.lineItems')->orderBy('created_at', 'desc')->get();
        return view('frontend.traveler.trips', compact('trips'));
    }

    public function show(Trip $trip)
    {
        $traveler = auth('traveler')->user();
        if ($trip->traveler_account_id !== $traveler->id) {
            abort(403);
        }
        $trip->load('bookings.lineItems.travellers', 'travellers');
        
        // Load associated accommodation and activity bookings
        $accommodationBookings = \App\Models\AccommodationBooking::where('trip_id', $trip->id)
            ->with(['accommodation', 'room', 'guests'])
            ->orderBy('check_in_date', 'asc')
            ->get();
        
        $activityBookings = \App\Models\ActivityBooking::where('trip_id', $trip->id)
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
            $booking->load(['activity.operator', 'activity.operationsStaffing']);
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

        // If no guests added yet, populate from saved guests based on adults/children
        if ($booking->guests->isEmpty()) {
            $guests = [];
            $savedGuestsArray = $savedGuests->toArray();
            $index = 0;
            for ($i = 0; $i < $booking->adults; $i++) {
                if (isset($savedGuestsArray[$index])) {
                    $guests[] = (object) $savedGuestsArray[$index];
                    $index++;
                }
            }
            for ($i = 0; $i < $booking->children; $i++) {
                if (isset($savedGuestsArray[$index])) {
                    $guests[] = (object) $savedGuestsArray[$index];
                    $index++;
                }
            }
            $booking->guests = collect($guests);
        }

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

        return view('frontend.traveler.manage-guests', compact('trip', 'booking', 'savedGuests', 'countries'));
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
                ->with(['activity.operator', 'activity.operationsStaffing', 'guests'])
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
        $guestRows = '';
        foreach ($guestsForVoucher as $guest) {
            $guestRows .= '<tr>' .
                '<td>' . e(trim($guest->first_name . ' ' . ($guest->last_name ?? ''))) . '</td>' .
                '<td>' . e($guest->nationality ?? '-') . '</td>' .
                '<td>' . ($guest->dob ? e(optional($guest->dob)->format('d/m/Y')) : '-') . '</td>' .
                '</tr>';
        }

        $html = '<h1 style="font-size:18px;">' . e($isActivity ? 'Activity' : 'Accommodation') . ' Voucher</h1>' .
            '<p><strong>Booking Reference:</strong> ' . e($booking->booking_reference) . '</p>' .
            '<p><strong>Trip:</strong> ' . e($trip->trip_name) . '</p>' .
            '<p><strong>' . e($isActivity ? 'Activity' : 'Accommodation') . ':</strong> ' . e($serviceName) . '</p>' .
            '<p><strong>Date:</strong> ' . e($voucherDate) . '</p>' .
            '<p><strong>' . e($variantName) . '</strong></p>' .
            '<p>' . ($duration ? e($duration) : '') . '</p>' .
            '<h2 style="font-size:16px; margin-top:18px;">' . e($isActivity ? 'Activity' : 'Accommodation') . ' Details</h2>' .
            '<p>' . $overview . '</p>' .
            '<p><strong>' . e($isActivity ? 'Meeting Point' : 'Check-in/out Details') . ':</strong><br>' . $meetingPoint . '</p>' .
            '<h2 style="font-size:16px; margin-top:18px;">Participant Details</h2>' .
            '<table border="1" cellpadding="6" cellspacing="0" width="100%" style="border-collapse:collapse;">' .
            '<thead><tr><th>Name</th><th>Nationality</th><th>Date of Birth</th></tr></thead>' .
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
        ]);

        // Delete existing guests
        $booking->guests()->delete();

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
        foreach (array_values($request->guests) as $index => $guestData) {
            $guestData['guest_number'] = $index + 1;
            $guestData['gender'] = $genderMapping[$guestData['gender']] ?? $guestData['gender'];
            $guestData['booking_type'] = $booking instanceof \App\Models\AccommodationBooking ? 'accommodation' : 'activity';
            $booking->guests()->create($guestData);
        }

        return redirect()->route('traveler.trip.detail', $trip)->with('success', 'Guests updated successfully.');
    }
}
