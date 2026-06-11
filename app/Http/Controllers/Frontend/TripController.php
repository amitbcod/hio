<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SavedGuest;
use App\Models\Trip;
use App\Models\ActivityBooking;
use App\Models\AccommodationBooking;
use App\Services\TripStatusService;
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

        // Classify trips into ongoing and past
        $classified = TripStatusService::classifyTrips($trips);
        $ongoingTrips = $classified['ongoing'];
        $pastTrips = $classified['past'];

        return view('frontend.traveler.trips', compact('trips', 'ongoingTrips', 'pastTrips'))->with('guestMode', false);
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
        $guestNames = [];
        foreach ($guestsForVoucher as $index => $guest) {
            $fullName = trim($guest->first_name . ' ' . ($guest->last_name ?? ''));
            $guestNames[] = $fullName;
            $guestRows .= '<tr>' .
                '<td>' . e($fullName ?: '-') . '</td>' .
                '<td>' . e($guest->nationality ?? '-') . '</td>' .
                '<td>' . ($guest->dob ? e(optional($guest->dob)->format('d/m/Y')) : '-') . '</td>' .
                '</tr>';
        }

        $issueDate = now()->format('d M Y');
        $serviceDate = $isActivity ? optional($booking->activity_date)->format('d M Y') : (optional($booking->check_in_date)->format('d M Y') . ' - ' . optional($booking->check_out_date)->format('d M Y'));
        $checkInDisplay = $isActivity ? optional($booking->activity_date)->format('d M Y') . ' • ' . ($booking->activity_time ?? '-') : optional($booking->check_in_date)->format('d M Y') . ' • From ' . ($booking->check_in_time ?? '14:00');
        $checkOutDisplay = $isActivity ? ($activity->duration ? e($activity->duration) : '-') : optional($booking->check_out_date)->format('d M Y') . ' • By ' . ($booking->check_out_time ?? '11:00');
        $nights = '-';
        if ($isAccommodation && $booking->check_in_date && $booking->check_out_date) {
            $diff = $booking->check_in_date->diffInDays($booking->check_out_date);
            $nights = $diff . ' Night' . ($diff === 1 ? '' : 's');
        }

        $responsibleName = $traveler->name ?? trim($booking->guests->first()->first_name . ' ' . ($booking->guests->first()->last_name ?? '')) ?? '-';
        $responsibleMobile = $traveler->phone ?? $booking->traveler_mobile ?? $booking->guest_mobile ?? '-';
        $responsibleEmail = $traveler->email ?? $booking->traveler_email ?? $booking->guest_email ?? '-';
        $adultCount = $booking->adults ?? $booking->adult_count ?? null;
        $childCount = $booking->children ?? $booking->children_count ?? null;
        $partySizeParts = [];
        if ($adultCount !== null) {
            $partySizeParts[] = (int) $adultCount . ' Adults';
        }
        if ($childCount !== null) {
            $partySizeParts[] = (int) $childCount . ' Children';
        }
        if (empty($partySizeParts)) {
            $partySizeParts[] = count($guestsForVoucher) . ' Traveller' . (count($guestsForVoucher) === 1 ? '' : 's');
        }
        $travelPartySize = implode(' • ', $partySizeParts);
        $otherTravellers = count($guestNames) > 1 ? e(implode(', ', array_slice($guestNames, 1))) : '-';

        $providerName = $accommodation->property_name ?? ($activity->activity_name ?? 'Service Provider');
        $providerAddress = trim(implode(', ', array_filter([
            $accommodation->address_line_1 ?? null,
            $accommodation->address_line_2 ?? null,
            $accommodation->city ?? null,
            $accommodation->country ?? null,
        ])), ', ');
        $providerAddress = $providerAddress ?: '-';
        $emergencyContact = $accommodation->emergency_contact_phone ?? $operator->emergency_contact_phone ?? '-';
        $receptionContact = $accommodation->reception_contact_phone ?? $operator->reception_contact_phone ?? '-';
        $locationLabel = $accommodation->country ?? 'Mauritius';
        $poweredLogoPath = public_path('images/holidays-io-logo.png');
        if (!file_exists($poweredLogoPath)) {
            $poweredLogoPath = '';
        } elseif (preg_match('/\.png$/i', $poweredLogoPath)) {
            $poweredLogoPath = $this->getSanitizedPngForTcpdf($poweredLogoPath);
        }
        $voucherUrl = $guestId
            ? route('traveler.trip.booking.download-voucher', ['trip' => $trip->id, 'booking' => $booking->id, 'guest' => $guestId], true)
            : route('traveler.trip.booking.download-voucher', ['trip' => $trip->id, 'booking' => $booking->id], true);

        $roomType = $room->room_name ?? $booking->room_name ?? '-';
        $occupancy = $adultCount !== null ? (int) $adultCount . ' Adults' . ($childCount ? ' • ' . (int) $childCount . ' Children' : '') : '-';
        $mealPlan = $booking->meal_plan ?? $booking->package_name ?? 'N/A';
        $specialRequests = $booking->special_request ?? $booking->special_requests ?? 'None';
        $bookingNotes = $booking->notes ?? $booking->booking_notes ?? '-';

        $operatorBusinessName = e($operator->business_name ?? $providerName);
        $poweredLogoHtml = $poweredLogoPath
            ? '<img src="' . $poweredLogoPath . '" style="max-width:120px; max-height:46px;" alt="Holidays.io logo">'
            : '<div style="font-size:18px;font-weight:700;color:#f7971e;">Holidays.io</div>';
        $locationLabelSafe = e($locationLabel ?? 'Mauritius');
        $voucherTitle = e($isActivity ? 'Activity Service Voucher' : 'Accommodation Service Voucher');
        $bookingReferenceSafe = e($booking->booking_reference ?? '-');
        $issueDateSafe = e($issueDate);
        $serviceDateSafe = e($serviceDate);
        $serviceTypeLabel = e($isActivity ? 'Activity' : 'Accommodation');
        $serviceDurationLabel = e($isActivity ? 'Number of Days' : 'Number of Nights');
        $serviceTypeDetailLabel = e($isActivity ? 'Activity Type' : 'Room Type');
        $responsibleNameSafe = e($responsibleName);
        $responsibleMobileSafe = e($responsibleMobile);
        $responsibleEmailSafe = e($responsibleEmail);
        $travelPartySizeSafe = e($travelPartySize);
        $otherTravellersSafe = e($otherTravellers);
        $providerNameSafe = e($providerName);
        $providerAddressSafe = e($providerAddress);
        $emergencyContactSafe = e($emergencyContact);
        $receptionContactSafe = e($receptionContact);
        $serviceNameSafe = e($serviceName);
        $checkInDisplaySafe = e($checkInDisplay);
        $checkOutDisplaySafe = e($checkOutDisplay);
        $nightsSafe = e($nights);
        $roomTypeSafe = e($roomType);
        $occupancySafe = e($occupancy);
        $mealPlanSafe = e($mealPlan);
        $specialRequestsSafe = e($specialRequests);
        $bookingNotesSafe = e($bookingNotes);

        $html = <<<HTML
<style>
body{font-family:helvetica;color:#222; font-size:10px;}
.section-title{font-size:13px;color:#0b2b51;margin:0 0 8px 0;font-weight:700;}
.label{font-size:8px;color:#5f6d7a;text-transform:uppercase;letter-spacing:0.5px;margin:0 0 4px 0;}
.value{font-size:11px;font-weight:700;margin:0;}
.info-card{border:1px solid #d7e4f0;border-radius:10px;background:#f7fafd;padding:10px;margin-bottom:8px;}
.info-card strong{display:block;font-size:11px;color:#0b2b51;margin-bottom:4px;}
.box{border:1px solid #d7e4f0;border-radius:12px;padding:14px;margin-bottom:12px;background:#fff;}
.accent-box{border-left:4px solid #f7971e;}
.small-text{font-size:9px;color:#5f6d7a;line-height:1.5;}
.footer-box{border:1px solid #d7e4f0;background:#eef4fb;border-radius:12px;padding:12px;margin-top:14px;}
.badge{display:inline-block;padding:5px 10px;border-radius:12px;font-size:9px;color:#1f3f61;background:#dceaf8;margin-right:6px;}
.check-list{margin:0;padding-left:18px;color:#33475b;line-height:1.6;}
.check-list li{margin-bottom:6px;}
.info-row td{padding:4px;vertical-align:top;}
.table-grid{width:100%;border-collapse:collapse;margin-top:8px;}
.table-grid td,.table-grid th{border:1px solid #d7e4f0;padding:8px;vertical-align:top;}
.table-grid th{background:#eef4fb;text-align:left;}
.two-col{width:49%;display:inline-block;vertical-align:top;}
.two-col + .two-col{margin-left:2%;}
</style>
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td width="65%" style="vertical-align:top;">
    <div style="border:1px dashed #cfd9e6;border-radius:12px;padding:16px;text-align:center;min-height:90px;">
        <div style="font-size:16px;font-weight:700;color:#0b2b51;">MPO LOGO</div>
        <div style="font-size:10px;color:#6a7b91;margin-top:6px;">{$operatorBusinessName}</div>
    </div>
</td>
<td width="35%" style="text-align:right;vertical-align:top;">
    <div style="display:inline-block;padding:10px 12px;border:1px solid #d7e4f0;border-radius:12px;background:#ffffff;">
        <div style="font-size:11px;color:#5f6d7a;margin-bottom:6px;">Powered by</div>
        {$poweredLogoHtml}
    </div>
</td>
</tr>
</table>
<div style="margin-top:16px;">
    <span class="badge">{$locationLabelSafe}</span>
    <span class="badge" style="background:#c6e9ce;color:#1b5e20;">Confirmed</span>
</div>
<h1 style="font-size:22px;margin:12px 0 4px 0;color:#0b2b51;">{$voucherTitle}</h1>
<div style="font-size:10px;color:#5f6d7a;margin-bottom:16px;">Voucher No: {$bookingReferenceSafe} | Issue Date: {$issueDateSafe}</div>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
<tr>
<td width="16.66%" style="padding:4px;"><div class="info-card"><strong>Voucher No.</strong>{$bookingReferenceSafe}</div></td>
<td width="16.66%" style="padding:4px;"><div class="info-card"><strong>Booking Ref.</strong>{$bookingReferenceSafe}</div></td>
<td width="16.66%" style="padding:4px;"><div class="info-card"><strong>Issue Date</strong>{$issueDateSafe}</div></td>
<td width="16.66%" style="padding:4px;"><div class="info-card"><strong>Service Date</strong>{$serviceDateSafe}</div></td>
<td width="16.66%" style="padding:4px;"><div class="info-card"><strong>Service Type</strong>{$serviceTypeLabel}</div></td>
<td width="16.66%" style="padding:4px;"><div class="info-card"><strong>Payment Status</strong>Paid</div></td>
</tr>
</table>

<div class="box accent-box">
    <div class="section-title">Responsible Traveller</div>
    <table width="100%" class="info-row" cellpadding="0" cellspacing="0">
        <tr>
            <td width="33%"><div class="label">Full Name</div><div class="value">{$responsibleNameSafe}</div></td>
            <td width="33%"><div class="label">Mobile / WhatsApp</div><div class="value">{$responsibleMobileSafe}</div></td>
            <td width="33%"><div class="label">Email</div><div class="value">{$responsibleEmailSafe}</div></td>
        </tr>
    </table>
    <div class="small-text" style="margin-top:10px;">
        <strong>Travel Party Size:</strong> {$travelPartySizeSafe}<br>
        <strong>Other Travellers:</strong> {$otherTravellersSafe}
    </div>
</div>

<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td width="50%" style="padding-right:8px;vertical-align:top;">
    <div class="box">
        <div class="section-title">Service Provider / Property</div>
        <div class="small-text"><strong>{$providerNameSafe}</strong><br>{$providerAddressSafe}</div>
        <div style="margin-top:10px;"><strong>Emergency Contact (24/7)</strong><br>{$emergencyContactSafe}</div>
        <div style="margin-top:6px;"><strong>Reception / Service Contact</strong><br>{$receptionContactSafe}</div>
    </div>
</td>
<td width="50%" style="padding-left:8px;vertical-align:top;">
    <div class="box">
        <div class="section-title">Service Details</div>
        <table width="100%" class="info-row" cellpadding="0" cellspacing="0">
            <tr><td class="label" width="40%">Property Name</td><td>{$serviceNameSafe}</td></tr>
            <tr><td class="label">Check-in Date / Time</td><td>{$checkInDisplaySafe}</td></tr>
            <tr><td class="label">Check-out Date / Time</td><td>{$checkOutDisplaySafe}</td></tr>
            <tr><td class="label">{$serviceDurationLabel}</td><td>{$nightsSafe}</td></tr>
            <tr><td class="label">{$serviceTypeDetailLabel}</td><td>{$roomTypeSafe}</td></tr>
            <tr><td class="label">Occupancy</td><td>{$occupancySafe}</td></tr>
            <tr><td class="label">Meal Plan</td><td>{$mealPlanSafe}</td></tr>
            <tr><td class="label">Special Requests</td><td>{$specialRequestsSafe}</td></tr>
            <tr><td class="label">Booking Notes</td><td>{$bookingNotesSafe}</td></tr>
        </table>
    </div>
</td>
</tr>
</table>

<div class="box">
    <div class="section-title">Important Information / Conditions</div>
    <ul class="check-list">
        <li>Please present this voucher on arrival at the property.</li>
        <li>All travellers must carry a valid passport or national ID.</li>
        <li>Check-in time: From 14:00 • Check-out time: By 11:00.</li>
        <li>Early check-in / late check-out are subject to availability.</li>
        <li>All amendments and cancellations are subject to the property's booking conditions.</li>
        <li>For any assistance during your stay, contact the MPO using the details below.</li>
    </ul>
    <div style="border:1px solid #d7e4f0;background:#eef4fb;border-radius:10px;padding:10px;margin-top:10px;" class="small-text">
        <strong>Your Local Connection</strong> – We’re here to support you. For enquiries or assistance during your trip, please contact us.
    </div>
</div>

<div class="footer-box">
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td width="60%" style="vertical-align:top;">
    <div style="font-size:13px;font-weight:700;color:#0b2b51;">MPO Support & Emergency</div>
    <div class="small-text" style="margin-top:8px;">
        <strong>Phone:</strong> +230 52 51 11 53<br>
        <strong>Email:</strong> support@lrt.mu<br>
        Available: 08:00 – 20:00 (Mauritius Time)
    </div>
</td>
<td width="40%" style="vertical-align:top;padding-left:10px;">
    <div style="border:1px solid #d7e4f0;border-radius:10px;padding:12px;text-align:center;">
        <div style="font-size:13px;font-weight:700;color:#f7971e;margin-bottom:8px;">Scan for Digital Voucher / Travel Wallet</div>
        <div style="width:120px;height:120px;margin:0 auto 10px auto;border:1px solid #d7e4f0;border-radius:10px;"></div>
        <div class="small-text">Present this QR code on your mobile device.</div>
    </div>
</td>
</tr>
</table>
</div>
HTML;

        $pdf = new \TCPDF();
        $pdf->SetCreator('Holidaysio');
        $pdf->SetAuthor($traveler->name ?? $traveler->first_name ?? 'Traveler');
        $pdf->SetTitle('Voucher - ' . ($booking->booking_reference ?? ($isActivity ? 'activity' : 'accommodation')));
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML($html, true, false, true, false, '');
        if (method_exists($pdf, 'write2DBarcode')) {
            $x = 150;
            $y = 120;
            $pdf->write2DBarcode($voucherUrl, 'QRCODE,H', $x, $y, 35, 35, [], 'N');
        }
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
            // Removed time_slot validation - timeslots are set from activity page
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

        // Removed participant_time_slots update - timeslots are set from activity page and apply to all participants

        return redirect()->route('traveler.trip.booking.manage-guests', ['trip' => $trip->id, 'booking' => $booking->id])->with('success', 'Guests updated successfully.');
    }

    protected function getSanitizedPngForTcpdf(string $pngPath): string
    {
        if (!file_exists($pngPath) || !extension_loaded('gd')) {
            return $pngPath;
        }

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0777, true);
        }

        $sanitizedPath = $tmpDir . '/holidays-io-logo.png';
        if (file_exists($sanitizedPath) && filemtime($sanitizedPath) >= filemtime($pngPath)) {
            return $sanitizedPath;
        }

        $image = @imagecreatefrompng($pngPath);
        if (!$image) {
            return $pngPath;
        }

        imagesavealpha($image, true);
        imagepng($image, $sanitizedPath, 9);
        imagedestroy($image);

        return file_exists($sanitizedPath) ? $sanitizedPath : $pngPath;
    }
}
