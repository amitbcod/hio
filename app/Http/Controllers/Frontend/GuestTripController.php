<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\GuestOtpToken;
use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\Trip;
use Illuminate\Http\Request;

class GuestTripController extends Controller
{
    /**
     * Show guest trip listing using verification link  
     */
    public function show($otp)
    {
        $otpToken = $this->resolveGuestToken($otp);
        if (!$otpToken) {
            return redirect()->route('frontend.home')
                ->with('error', 'Invalid or expired verification link. Please check your email for a new link.');
        }

        $this->authenticateGuest($otpToken);

        // Try to get trips first
        $tripIds = $this->getGuestTripIds($otpToken);

        if (empty($tripIds)) {
            $this->attachTripsToGuestBookings($otpToken);
            $tripIds = $this->getGuestTripIds($otpToken);
        }

        if (!empty($tripIds)) {
            // Show trips in table view (same as authenticated customers)
            $trips = Trip::with([
                'accommodationBookings.guests',
                'activityBookings.guests'
            ])
                ->whereIn('id', $tripIds)
                ->orderBy('start_date', 'desc')
                ->get();

            return view('frontend.traveler.trips', compact('trips', 'otp'))
                ->with('guestMode', true);
        }

        // Fallback: Show bookings directly if no trips
        [$accommodationBookings, $activityBookings] = $this->getGuestBookings($otpToken);

        if ($accommodationBookings->isEmpty() && $activityBookings->isEmpty()) {
            return view('frontend.guest-trip', compact('otpToken'))
                ->with('guestMode', true);
        }

        return view('frontend.guest-trip', compact('otpToken', 'accommodationBookings', 'activityBookings'))
            ->with('guestMode', true);
    }

    public function showTrip($otp, Trip $trip)
    {
        $otpToken = $this->resolveGuestToken($otp);
        if (!$otpToken || !$this->tripBelongsToGuest($trip, $otpToken)) {
            abort(403);
        }

        $this->authenticateGuest($otpToken);

        $trip->load('bookings.lineItems.travellers', 'travellers');

        $accommodationBookings = AccommodationBooking::where('trip_id', $trip->id)
            ->with(['accommodation', 'room', 'guests'])
            ->orderBy('check_in_date', 'asc')
            ->get();

        $activityBookings = ActivityBooking::where('trip_id', $trip->id)
            ->with(['activity', 'guests'])
            ->orderBy('activity_date', 'asc')
            ->get();

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

        return view('frontend.traveler.trip-detail', compact(
            'trip',
            'accommodationBookings',
            'activityBookings',
            'tripStartDate',
            'tripEndDate',
            'otp'
        ))->with('guestMode', true);
    }

    public function downloadVoucher($otp, Trip $trip, $bookingId, $guestId = null)
    {
        $otpToken = $this->resolveGuestToken($otp);
        if (!$otpToken || !$this->tripBelongsToGuest($trip, $otpToken)) {
            abort(403);
        }

        $this->authenticateGuest($otpToken);

        $booking = AccommodationBooking::where('id', $bookingId)
            ->where('trip_id', $trip->id)
            ->with(['accommodation', 'room', 'guests'])
            ->first();

        if (!$booking) {
            $booking = ActivityBooking::where('id', $bookingId)
                ->where('trip_id', $trip->id)
                ->with(['activity.operator', 'activity.operationsStaffing', 'activity.schedulingTimeSlots', 'guests'])
                ->first();
        }

        if (!$booking) {
            abort(404);
        }

        $isActivity = $booking instanceof ActivityBooking;
        $isAccommodation = $booking instanceof AccommodationBooking;

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
                $meetingPoint = preg_replace('/\r\n|\r|\n/', '<br>', $meetingPoint);
            }
            if ($overview !== 'Not available') {
                $overview = preg_replace('/\r\n|\r|\n/', '<br>', $overview);
            }
        } elseif ($isAccommodation) {
            $meetingPoint = 'Check-in: ' . optional($booking->check_in_date)->format('d/m/Y') . '<br>Check-out: ' . optional($booking->check_out_date)->format('d/m/Y');
            $overview = $accommodation->property_description ? strip_tags($accommodation->property_description, $allowedTags) : 'Not available';
            if ($overview !== 'Not available') {
                $overview = preg_replace('/\r\n|\r|\n/', '<br>', $overview);
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

            $opsContact = [];
        }

        $voucherGuest = null;
        if ($guestId) {
            $voucherGuest = $booking->guests->firstWhere('id', $guestId);
            if (!$voucherGuest) {
                abort(404);
            }
        }

        $guestsForVoucher = $voucherGuest ? [$voucherGuest] : $booking->guests->all();
        
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

        $responsibleName = trim($guestsForVoucher[0]->first_name . ' ' . ($guestsForVoucher[0]->last_name ?? '')) ?: '-';
        $responsibleMobile = $booking->guest_mobile ?? $booking->guest_phone ?? '-';
        $responsibleEmail = $otpToken->email ?? $booking->guest_email ?? '-';
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

        $roomType = $room->room_name ?? $booking->room_name ?? '-';
        $occupancy = $adultCount !== null ? (int) $adultCount . ' Adults' . ($childCount ? ' • ' . (int) $childCount . ' Children' : '') : '-';
        $mealPlan = $booking->meal_plan ?? $booking->package_name ?? 'N/A';
        $specialRequests = $booking->special_request ?? $booking->special_requests ?? 'None';
        $bookingNotes = $booking->notes ?? $booking->booking_notes ?? '-';

        $operatorLabel = e($operator->business_name ?? $providerName);
        $locationLabelSafe = e('Mauritius');
        $voucherTitle = e($isActivity ? 'Activity Service Voucher' : 'Accommodation Service Voucher');
        $bookingReferenceSafe = e($booking->booking_reference ?? '-');
        $issueDateSafe = e($issueDate);
        $serviceDateSafe = e($serviceDate);
        $serviceTypeLabel = e($isActivity ? 'Activity' : 'Accommodation');
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
        $infoLabelCheckIn = e($isActivity ? 'Activity Date / Time' : 'Check-in Date / Time');
        $infoLabelCheckOut = e($isActivity ? 'Finish / Duration' : 'Check-out Date / Time');
        $infoLabelDaysNights = e($isActivity ? 'Number of Days' : 'Number of Nights');
        $infoLabelType = e($isActivity ? 'Activity Type' : 'Room Type');

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
        <div style="font-size:10px;color:#6a7b91;margin-top:6px;">{$operatorLabel}</div>
    </div>
</td>
<td width="35%" style="text-align:right;vertical-align:top;">
    <div style="display:inline-block;padding:10px 12px;border:1px solid #d7e4f0;border-radius:12px;background:#ffffff;">
        <div style="font-size:11px;color:#5f6d7a;margin-bottom:6px;">Powered by</div>
        <div style="font-size:18px;font-weight:700;color:#f7971e;">Holidays.io</div>
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
            <tr><td class="label">{$infoLabelCheckIn}</td><td>{$checkInDisplaySafe}</td></tr>
            <tr><td class="label">{$infoLabelCheckOut}</td><td>{$checkOutDisplaySafe}</td></tr>
            <tr><td class="label">{$infoLabelDaysNights}</td><td>{$nightsSafe}</td></tr>
            <tr><td class="label">{$infoLabelType}</td><td>{$roomTypeSafe}</td></tr>
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
    <div style="border:1px solid #d7e4f0;background:#eef4f...END
HTML;

        $pdf->SetCreator('Holidaysio');
        $pdf->SetAuthor($otpToken->email ?? 'Guest');
        $pdf->SetTitle('Voucher - ' . ($booking->booking_reference ?? ($isActivity ? 'activity' : 'accommodation')));
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML($html, true, false, true, false, '');
        if (method_exists($pdf, 'write2DBarcode')) {
            $x = 150;
            $y = 120;
            $pdf->write2DBarcode($voucherUrl ?? '', 'QRCODE,H', $x, $y, 35, 35, [], 'N');
        }
        $filename = ($isActivity ? 'activity' : 'accommodation') . '-voucher-' . preg_replace('/[^A-Za-z0-9_-]/', '', $booking->booking_reference) . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    protected function getGuestTripIds(GuestOtpToken $otpToken)
    {
        $accommodationTripIds = AccommodationBooking::where('guest_email', $otpToken->email)
            ->where('is_guest', 1)
            ->pluck('trip_id')
            ->filter()
            ->unique()
            ->toArray();

        $activityTripIds = ActivityBooking::where('guest_email', $otpToken->email)
            ->where('is_guest', 1)
            ->pluck('trip_id')
            ->filter()
            ->unique()
            ->toArray();

        $tokenAccommodationTripIds = AccommodationBooking::where('guest_otp_token_id', $otpToken->id)
            ->pluck('trip_id')
            ->filter()
            ->unique()
            ->toArray();

        $tokenActivityTripIds = ActivityBooking::where('guest_otp_token_id', $otpToken->id)
            ->pluck('trip_id')
            ->filter()
            ->unique()
            ->toArray();

        return array_values(array_unique(array_merge(
            $accommodationTripIds,
            $activityTripIds,
            $tokenAccommodationTripIds,
            $tokenActivityTripIds
        )));
    }

    protected function getGuestBookings(GuestOtpToken $otpToken)
    {
        $accommodationBookings = AccommodationBooking::where(function ($query) use ($otpToken) {
                $query->where('guest_email', $otpToken->email)
                      ->where('is_guest', 1);
            })
            ->orWhere('guest_otp_token_id', $otpToken->id)
            ->with(['accommodation', 'room', 'guests'])
            ->orderBy('check_in_date', 'asc')
            ->get();

        $activityBookings = ActivityBooking::where(function ($query) use ($otpToken) {
                $query->where('guest_email', $otpToken->email)
                      ->where('is_guest', 1);
            })
            ->orWhere('guest_otp_token_id', $otpToken->id)
            ->with(['activity', 'guests'])
            ->orderBy('activity_date', 'asc')
            ->get();

        return [$accommodationBookings, $activityBookings];
    }

    protected function attachTripsToGuestBookings(GuestOtpToken $otpToken)
    {
        $accommodationBookings = AccommodationBooking::whereNull('trip_id')
            ->where(function ($query) use ($otpToken) {
                $query->where('guest_otp_token_id', $otpToken->id)
                      ->orWhere(function ($subQuery) use ($otpToken) {
                          $subQuery->where('guest_email', $otpToken->email)
                                   ->where('is_guest', 1);
                      });
            })
            ->get();

        $activityBookings = ActivityBooking::whereNull('trip_id')
            ->where(function ($query) use ($otpToken) {
                $query->where('guest_otp_token_id', $otpToken->id)
                      ->orWhere(function ($subQuery) use ($otpToken) {
                          $subQuery->where('guest_email', $otpToken->email)
                                   ->where('is_guest', 1);
                      });
            })
            ->get();

        $allBookings = $accommodationBookings->concat($activityBookings);

        foreach ($allBookings as $booking) {
            if ($booking->trip_id) {
                continue;
            }

            $startDate = $booking->check_in_date ?? $booking->activity_date ?? now()->toDateString();
            $endDate = $booking->check_out_date ?? $booking->activity_date ?? $startDate;
            $title = $booking instanceof AccommodationBooking ? 'Accommodation Trip' : 'Activity Trip';

            $trip = Trip::create([
                'traveler_account_id' => null,
                'title' => $title,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'planned',
            ]);

            $booking->trip_id = $trip->id;
            $booking->save();
        }
    }

    protected function resolveGuestToken($otp)
    {
        $token = GuestOtpToken::where('otp_code', $otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$token && session('guest_trip_access') && session('guest_otp_token_id')) {
            $token = GuestOtpToken::find(session('guest_otp_token_id'));
        }

        return $token;
    }

    protected function authenticateGuest(GuestOtpToken $otpToken)
    {
        if (!$otpToken->is_verified) {
            $otpToken->verify();
        }

        if (!session('guest_trip_access')) {
            session([
                'guest_email' => $otpToken->email,
                'guest_otp_token_id' => $otpToken->id,
                'guest_trip_access' => true,
            ]);
        }
    }

    protected function resolveGuestTrip(GuestOtpToken $otpToken)
    {
        $tripId = $otpToken->booking?->trip_id;

        if (!$tripId) {
            $activityBooking = ActivityBooking::where('guest_otp_token_id', $otpToken->id)->first();
            $tripId = $activityBooking?->trip_id;
        }

        if (!$tripId) {
            $booking = AccommodationBooking::where('guest_email', $otpToken->email)
                ->where('is_guest', 1)
                ->first();
            $tripId = $booking?->trip_id;
        }

        if (!$tripId) {
            $booking = ActivityBooking::where('guest_email', $otpToken->email)
                ->where('is_guest', 1)
                ->first();
            $tripId = $booking?->trip_id;
        }

        return $tripId ? Trip::find($tripId) : null;
    }

    protected function tripBelongsToGuest(Trip $trip, GuestOtpToken $otpToken)
    {
        return AccommodationBooking::where('trip_id', $trip->id)
                ->where('guest_email', $otpToken->email)
                ->exists()
            || ActivityBooking::where('trip_id', $trip->id)
                ->where('guest_email', $otpToken->email)
                ->exists();
    }

    protected function findBookingForTrip(Trip $trip, $bookingId)
    {
        $booking = AccommodationBooking::where('id', $bookingId)
            ->where('trip_id', $trip->id)
            ->with('guests')
            ->first();

        if (!$booking) {
            $booking = ActivityBooking::where('id', $bookingId)
                ->where('trip_id', $trip->id)
                ->with('guests')
                ->first();
        }

        return $booking;
    }

    public function manageGuests($otp, Trip $trip, $bookingId)
    {
        $otpToken = $this->resolveGuestToken($otp);
        if (!$otpToken || !$this->tripBelongsToGuest($trip, $otpToken)) {
            abort(403);
        }

        $this->authenticateGuest($otpToken);

        $booking = $this->findBookingForTrip($trip, $bookingId);
        if (!$booking) {
            abort(404);
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

        $activityTimeSlots = [];
        if ($booking instanceof ActivityBooking && $booking->activity) {
            $activityTimeSlots = $booking->activity->schedulingTimeSlots ?? collect();
        }

        return view('frontend.traveler.manage-guests', compact(
            'trip',
            'booking',
            'countries',
            'activityTimeSlots',
            'otp'
        ))->with([ 'guestMode' => true, 'savedGuests' => collect() ]);
    }

    public function updateGuests(Request $request, $otp, Trip $trip, $bookingId)
    {
        $otpToken = $this->resolveGuestToken($otp);
        if (!$otpToken || !$this->tripBelongsToGuest($trip, $otpToken)) {
            abort(403);
        }

        $booking = $this->findBookingForTrip($trip, $bookingId);
        if (!$booking) {
            abort(404);
        }

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

        $booking->guests()->delete();

        $guestInput = $request->input('guests', []);
        if (!is_array($guestInput)) {
            $guestInput = [];
        }

        $genderMapping = [
            'Mr' => 'male',
            'Mrs' => 'female',
            'Ms' => 'female',
            'Male' => 'male',
            'Female' => 'female',
            'Non-binary' => 'non_binary',
            'Other' => 'other',
        ];

        foreach (array_values($guestInput) as $index => $guestData) {
            $guestData['guest_number'] = $index + 1;
            $guestData['gender'] = $genderMapping[$guestData['gender']] ?? $guestData['gender'];
            $guestData['booking_type'] = $booking instanceof ActivityBooking ? 'activity' : 'accommodation';
            $booking->guests()->create($guestData);
        }

        // Removed participant_time_slots update - timeslots are set from activity page and apply to all participants

        return redirect()->route('traveler.guest-trip.trip.booking.manage-guests', ['otp' => $otp, 'trip' => $trip->id, 'booking' => $booking->id])
            ->with('success', 'Guests updated successfully.');
    }

    public function confirmAddService(Request $request, $otp, Trip $trip)
    {
        $otpToken = $this->resolveGuestToken($otp);
        if (!$otpToken || !$this->tripBelongsToGuest($trip, $otpToken)) {
            abort(403);
        }

        $request->session()->put('add_to_trip_id', $trip->id);

        return redirect()->route('frontend.booking.cart')
            ->with('success', 'Services will be added to Trip ID: ' . $trip->id);
    }
}
