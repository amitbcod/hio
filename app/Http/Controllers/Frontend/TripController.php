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

        $firstGuest = $booking->guests->first();
        $responsibleName = $traveler->name ?? ($firstGuest ? trim(($firstGuest->first_name ?? '') . ' ' . ($firstGuest->last_name ?? '')) : null) ?? '-';
        $responsibleMobile = $traveler->phone ?? $booking->traveler_mobile ?? $booking->guest_mobile ?? ($firstGuest->phone ?? '-');
        $responsibleEmail = $traveler->email ?? $booking->traveler_email ?? $booking->guest_email ?? ($firstGuest->email ?? '-');
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
        if ($isActivity) {
            $providerAddress = trim(implode(', ', array_filter([
                $activity->destination ?? null,
                $activity->town ?? null,
                $activity->region ?? null,
                $operator->country ?? null,
            ])), ', ');
            $locationLabel = $activity->town ?: ($activity->region ?: ($operator->country ?? 'Mauritius'));
            $emergencyContact = $activity->emergency_contact_phone ?? $operator->emergency_contact_phone ?? null;
            $receptionContact = $activity->reception_contact_phone ?? $operator->reception_contact_phone ?? null;
        } else {
            $providerAddress = trim(implode(', ', array_filter([
                $accommodation->address_line_1 ?? null,
                $accommodation->address_line_2 ?? null,
                $accommodation->city ?? null,
                $accommodation->country ?? null,
            ])), ', ');
            $locationLabel = $accommodation->country ?? 'Mauritius';
            $emergencyContact = $accommodation->emergency_contact_phone ?? $operator->emergency_contact_phone ?? null;
            $receptionContact = $accommodation->reception_contact_phone ?? $operator->reception_contact_phone ?? null;
        }
        if (!$emergencyContact && !empty($reservationContact)) {
            $emergencyContact = implode(' | ', $reservationContact);
        }
        if (!$receptionContact && !empty($reservationContact)) {
            $receptionContact = implode(' | ', $reservationContact);
        }
        $providerAddress = $providerAddress ?: '-';
        $emergencyContact = $emergencyContact ?: '-';
        $receptionContact = $receptionContact ?: '-';
        $poweredLogoPath = public_path('images/holidays-io-logo.png');
        if (!file_exists($poweredLogoPath)) {
            $poweredLogoPath = '';
        } elseif (preg_match('/\.png$/i', $poweredLogoPath)) {
            $poweredLogoPath = $this->getSanitizedPngForTcpdf($poweredLogoPath);
        }

        $company = $this->getAdminCompanyData();
        $companyBusinessNameSafe = e($company['business_name']);
        $companyBusinessAddressSafe = e($company['business_address']);
        $companyEmailSafe = e($company['business_email']);
        $companyPhoneSafe = e($company['business_phone']);
        $companyVatSafe = e($company['vat_number']);
        $companyBrnSafe = e($company['brn_number']);
        $companyLogoHtml = $this->renderAdminCompanyLogoHtml($company['logo_path'], $company['business_name']);

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
            ? '<img src="' . $poweredLogoPath . '" width="100" height="40" style="width:100px; height:auto; display:block;" alt="Holidays.io logo">'
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
        $infoLabelCheckIn = e($isActivity ? 'Activity Date / Time' : 'Check-in Date / Time');
        $infoLabelCheckOut = e($isActivity ? 'Activity Date / Time' : 'Check-out Date / Time');
        $infoLabelDaysNights = e($isActivity ? 'Number of Days' : 'Number of Nights');
        $infoLabelType = e($isActivity ? 'Activity Type' : 'Room Type');
        $html = <<<HTML
<style>
    .label{font-size:8px;color:#5f6d7a;letter-spacing:0.5px;}
</style>
<table width="100%" border="0" style="color:#0b2b51;">
  <tbody>
    <tr>
      <td valign="top"><table width="100%" border="0">
        <tbody>
          <tr>
            <td width="68%" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td>{$companyLogoHtml}</td>
                </tr>
                <tr>
                  <td style="font-size:12px">Your Local Connection - Mauritius</td>
                </tr>
                <tr>
                  <td style="font-size:12px">{$companyBusinessNameSafe}</td>
                </tr>
                <tr>
                  <td style="color:#6a7b91;font-size:10px">{$companyBusinessAddressSafe}</td>
                </tr>
                <tr>
                  <td style="color:#6a7b91;font-size:10px">{$companyPhoneSafe} | {$companyEmailSafe}</td>
                </tr>
              </tbody>
            </table></td>
            <td width="32%" valign="top"><table width="100%" border="0">
              <tbody>
                <tr>
                  <td style="font-size:8px;color:#5f6d7a;">Powered by</td>
                </tr>
                <tr>
                  <td>{$poweredLogoHtml}</td>
                </tr>
              </tbody>
            </table></td>
          </tr>
        </tbody>
      </table>
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><h1 style="font-size:22px">{$voucherTitle}</h1></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td style="color:#5f6d7a">Voucher No: {$bookingReferenceSafe} | Issue Date: {$issueDateSafe} |  Status: <span style="color:#28a745;font-weight:bold;">Confirmed</span></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td width="49%" style="background-color: #f7fafd;border: 1px solid #d7e4f0;"><table width="100%" cellpadding="5" cellspacing="0">
            <tr>
              <td><strong>Booking Ref.</strong></td>
            </tr>
            <tr>
              <td>{$bookingReferenceSafe}</td>
            </tr>
          </table></td>
          <td style="width:2%">&nbsp;&nbsp;</td>
          <td width="49%" style="background-color: #f7fafd;border: 1px solid #d7e4f0;"><table width="100%" cellpadding="5" cellspacing="0">
            <tr>
              <td><strong>Service Date</strong></td>
            </tr>
            <tr>
              <td>{$serviceDateSafe}</td>
            </tr>
          </table></td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td style="font-size:13px;">Responsible Traveller</td>
    </tr>
    <tr>
      <td><table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td width="50%" style="color:#5f6d7a">Full Name</td>
          <td width="50%" style="color:#5f6d7a">Other Travellers</td>
        </tr>
        <tr>
          <td style="color:#5f6d7a">{$responsibleNameSafe}</td>
          <td style="color:#5f6d7a">{$otherTravellersSafe}</td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><strong>Travel Party Size:</strong> {$travelPartySizeSafe}</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td width="49%" valign="top" style="background-color: #f7fafd;border: 1px solid #d7e4f0;">
            <table width="100%" cellpadding="2" cellspacing="0">
              <tr>
                <td colspan="2" style="border-bottom:1px solid #dce7f5;"><strong style="font-size:12px">Service Details</strong></td>
				  </tr>
              <tr>
                <td class="label" width="40%">Property Name</td>
                <td><strong>{$serviceNameSafe}</strong></td>
              </tr>
              <tr>
                <td class="label">{$infoLabelCheckIn}</td>
                <td><strong>{$checkInDisplaySafe}</strong></td>
              </tr>
              <tr>
                <td class="label">{$infoLabelCheckOut}</td>
                <td><strong>{$checkOutDisplaySafe}</strong></td>
              </tr>
              <tr>
                <td class="label">{$infoLabelDaysNights}</td>
                <td>{$nightsSafe}</td>
              </tr>
              <tr>
                <td class="label">{$infoLabelType}</td>
                <td>{$roomTypeSafe}</td>
              </tr>
              <tr>
                <td class="label">Occupancy</td>
                <td>{$occupancySafe}</td>
              </tr>
              <tr>
                <td class="label">Meal Plan</td>
                <td>{$mealPlanSafe}</td>
              </tr>
              <tr>
                <td class="label">Special Requests</td>
                <td>{$specialRequestsSafe}</td>
              </tr>
              <tr>
                <td class="label">Booking Notes</td>
                <td>{$bookingNotesSafe}</td>
              </tr>
            </table>
          </td>
          <td style="width:2%">&nbsp;&nbsp;</td>
          <td width="49%" valign="top" style="background-color: #f7fafd;border: 1px solid #d7e4f0;">
            <table width="100%" cellpadding="2" cellspacing="0">
              <tr>
                <td style="border-bottom:1px solid #dce7f5;"><strong style="font-size:12px">Service Provider / Property</strong></td>
              </tr>
              <tr>
                <td>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr>
                        <td>{$providerNameSafe}</td>
                        </tr>
                        <tr>
                        <td>{$providerAddressSafe}</td>
                        </tr>
                    </tbody>
                    </table>
                </td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="label">Emergency Contact (24/7)</td>
              </tr>
              <tr>
                <td>{$emergencyContactSafe}</td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="label">Reception / Service Contact</td>
              </tr>
              <tr>
                <td>{$receptionContactSafe}</td>
              </tr>
            </table>
          </td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td style="font-size:13px"><strong>Important Information / Conditions</strong></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><ul class="check-list">
            <li>Please present this voucher on arrival at the property.</li>
            <li>All travellers must carry a valid passport or national ID.</li>
            <li>Early check-in / late check-out are subject to availability and at a servicee fee.</li>
            <li>All amendments and cancellations are subject to the property's booking conditions.</li>
            <li>For any assistance during your stay, contact the MPO using the details below.</li>
        </ul></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><strong>Your Local Connection</strong> We are here to support you. For enquiries or assistance during your trip, please contact us.</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td style="font-size:13px;"><strong>MPO Support and Emergency</strong></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>
      <table width="100%" border="0">
        <tbody>
          <tr>
            <td>Support Ticket within your account</td>
          </tr>
          <tr>
            <td>Office Hours: 09:00 - 17:30 </td>
          </tr>
          <tr>
            <td>Office : +230 427 10 60</td>
          </tr>
          <tr>
            <td>WhatsApp: +230 52 51 11 53 </td>
          </tr>
          <tr>
            <td>(After hours Emergency only)</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td><span style="color:#4a5f7f; font-size:8px;">We are here to help you before, during and after your trip.</span></td>
          </tr>
        </tbody>
      </table>  
      </td>
    </tr>
    <tr>
      <td align="center">&nbsp;</td>
    </tr>
    <tr>
      <td align="center" style="font-size:8px; color:#7a8a9f; border-top:1px solid #e1ecfa">
        
      <table width="100%" border="0" cellpadding="5" cellspacing="0">
        <tbody>
          <tr>
            <td align="center"><strong style="color:#0b2b51;">LRT Mauritius LTD </strong><br>
                Your Local Connection in Mauritius<br>
                <strong style="color:#0b2b51;">Powered by</strong> <span style="color:#f7971e; font-weight:700;">HOLIDAYS.IO</span></td>
          </tr>
        </tbody>
      </table>

        </td>
    </tr>
  </tbody>
</table>
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
          //  $pdf->write2DBarcode($voucherUrl, 'QRCODE,H', $x, $y, 35, 35, [], 'N');
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

    public function downloadInvoice(Trip $trip)
    {
        $traveler = auth('traveler')->user();
        if ($trip->traveler_account_id !== $traveler->id) {
            abort(403);
        }

        // Get all bookings for the trip
        $accommodationBookings = $trip->accommodationBookings ?? collect();
        $activityBookings = $trip->activityBookings ?? collect();
        
        $allBookings = $accommodationBookings->merge($activityBookings);
        
        if ($allBookings->isEmpty()) {
            abort(404);
        }

        // Load logo
        $poweredLogoPath = public_path('images/holidays-io-logo.png');
        if (!file_exists($poweredLogoPath)) {
            $poweredLogoPath = '';
        } elseif (preg_match('/\.png$/i', $poweredLogoPath)) {
            $poweredLogoPath = $this->getSanitizedPngForTcpdf($poweredLogoPath);
        }

        $company = $this->getAdminCompanyData();
        $companyBusinessNameSafe = e($company['business_name']);
        $companyBusinessAddressSafe = e($company['business_address']);
        $companyEmailSafe = e($company['business_email']);
        $companyPhoneSafe = e($company['business_phone']);
        $companyVatSafe = e($company['vat_number']);
        $companyBrnSafe = e($company['brn_number']);
        $companyLogoHtml = $this->renderAdminCompanyLogoHtml($company['logo_path'], $company['business_name']);

        // Build invoice data
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($trip->id, 6, '0', STR_PAD_LEFT);
        $invoiceDate = now()->format('d M Y');
        $bookingRef = 'B' . str_pad($trip->id, 4, '0', STR_PAD_LEFT);

        // Traveler details - safe escaping
        $travelerName = e($traveler->name ?? $traveler->first_name ?? 'Guest');
        $travelerPhone = e($traveler->phone ?? $traveler->mobile ?? $traveler->mobile_phone ?? $traveler->phone_number ?? $traveler->contact_number ?? $traveler->contact_phone ?? 'N/A');
        $travelerEmail = e($traveler->email ?? 'N/A');
        $travelerAddress = trim(implode(', ', array_filter([
            $traveler->address_line_1 ?? null,
            $traveler->address_line_2 ?? null,
            $traveler->city_region ?? null,
            $traveler->country ?? null,
        ])));
        $travelerAddress = e($travelerAddress ?: 'Address not provided');
        $accountId = 'TRV-' . str_pad($traveler->id, 6, '0', STR_PAD_LEFT);

        // Build invoice items with proper data
        $invoiceItems = [];
        $subtotal = 0;

        foreach ($accommodationBookings as $booking) {
            $amount = (float) data_get($booking, 'total_amount', $booking->total_price ?? 0);
            if ($booking->accommodation && $amount > 0) {
                $nights = $booking->check_in_date && $booking->check_out_date
                    ? (int) $booking->check_out_date->diffInDays($booking->check_in_date)
                    : 1;
                // Ensure nights is a positive integer (guard against swapped or invalid dates)
                $nights = max(1, abs($nights));
                $unitPrice = $amount;
                $totalForLine = $unitPrice * $nights;

                $item = [
                    'type' => 'Accommodation',
                    'name' => e($booking->accommodation->property_name ?? 'Accommodation'),
                    'location' => e($booking->accommodation->city ?? 'Mauritius'),
                    'checkIn' => $booking->check_in_date ? $booking->check_in_date->format('d M Y') : 'N/A',
                    'checkOut' => $booking->check_out_date ? $booking->check_out_date->format('d M Y') : 'N/A',
                    'description' => e(($booking->room->room_name ?? 'Room') . ' - ' . ($booking->meal_plan ?? 'Bed & Breakfast')),
                    'notes' => e('Room: ' . ($booking->room->room_name ?? 'Standard') . ' | Meal: ' . ($booking->meal_plan ?? 'Bed & Breakfast')),
                    'qty' => $nights,
                    'unitPrice' => $unitPrice,
                    'total' => $totalForLine,
                ];
                $invoiceItems[] = $item;
                $subtotal += $totalForLine;
            }
        }

        foreach ($activityBookings as $booking) {
            $amount = (float) data_get($booking, 'total_amount', $booking->total_price ?? 0);
            if ($booking->activity && $amount > 0) {
                $item = [
                    'type' => 'Activity',
                    'name' => e($booking->activity->activity_name ?? 'Activity'),
                    'location' => e($booking->activity->town ?? 'Mauritius'),
                    'checkIn' => $booking->activity_date ? $booking->activity_date->format('d M Y') : 'N/A',
                    'checkOut' => $booking->activity_date ? $booking->activity_date->format('d M Y') : 'N/A',
                    'description' => e($booking->variant_name ?? ($booking->activity->service_type ?? 'Activity')),
                    'notes' => e(($booking->variant_name ?? ($booking->activity->service_type ?? 'Activity')) . ' | ' . (($booking->guests ?? collect())->count() ?: 1) . ' pax'),
                    'qty' => 1,
                    'unitPrice' => $amount,
                    'total' => $amount,
                ];
                $invoiceItems[] = $item;
                $subtotal += $amount;
            }
        }

        // Calculate totals
        $discountPercent = 5;
        $discountAmount = ($subtotal * $discountPercent) / 100;
        $taxableAmount = $subtotal - $discountAmount;
        $vatPercent = 15;
        $vatAmount = ($taxableAmount * $vatPercent) / 100;
        $serviceFee = 325;
        $totalAmount = $taxableAmount + $vatAmount + $serviceFee;

        $formattedSubtotal = number_format($subtotal, 2);
        $formattedDiscountAmount = number_format($discountAmount, 2);
        $formattedTaxableAmount = number_format($taxableAmount, 2);
        $formattedVatAmount = number_format($vatAmount, 2);
        $formattedServiceFee = number_format($serviceFee, 2);
        $formattedTotalAmount = number_format($totalAmount, 2);

        // Build service rows
        $serviceRows = '';
        foreach ($invoiceItems as $index => $item) {
            $rowUnitPrice = number_format($item['unitPrice'], 2);
            $rowTax = number_format($item['total'] * 0.15, 2);
            $rowTotal = number_format($item['total'], 2);
            $serviceRows .= '<tr>

                <td style="width:22%;border-bottom:1px solid #dce7f5;padding:10px;">
                    <div style="font-weight:600;color:#0b2b51;margin-bottom:2px;">' . $item['type'] . '</div>
                    <div style="font-size:9px;color:#4a5f7f;margin-bottom:2px;"><strong>' . $item['name'] . '</strong></div>
                    <div style="font-size:9px;color:#7a8a9f;">' . $item['location'] . '</div>
                </td>
                <td style="width:18%;border-bottom:1px solid #dce7f5;padding:10px;font-size:9px;">
                    <div style="margin-bottom:4px;"><strong>' . $item['checkIn'] . '</strong></div>
                    <div style="color:#7a8a9f;">' . $item['checkOut'] . '</div>
                </td>
                <td style="width:20%;border-bottom:1px solid #dce7f5;padding:10px;font-size:9px;">
                    <div style="font-weight:600;margin-bottom:4px;">' . $item['description'] . '</div>
                    <div style="color:#7a8a9f;">' . $item['notes'] . '</div>
                </td>
                <td style="width:8%;border-bottom:1px solid #dce7f5;padding:10px;text-align:center;">' . $item['qty'] . '</td>
                <td style="width:10%;border-bottom:1px solid #dce7f5;padding:10px;text-align:center;font-weight:600;">' . $rowUnitPrice . '</td>
      
                <td style="width:8%;border-bottom:1px solid #dce7f5;padding:10px;text-align:center;font-weight:600;color:#0b2b51;">' . $rowTotal . '</td>
            </tr>';
        }

        $poweredLogoHtml = $poweredLogoPath
            ? '<img src="' . $poweredLogoPath . '" width="100" height="40" style="width:100px; height:auto; display:block;" alt="Holidays.io">'
            : '<span style="color:#f7971e;font-weight:700;font-size:14px;">HOLIDAYS.io</span>';

            $discountRow = '';

if ($discountAmount > 0) {
    $discountRow = '<div class="totals-row">
                        <span class="totals-label">Discount (' . $discountPercent . '%): </span>
                        <span class="totals-amount">-USD ' . $formattedDiscountAmount . '</span>
                    </div>';
                    $discountRow = trim($discountRow);
}
        $html = <<<HTML
<style>
body { font-family:helvetica; color:#222; font-size:10px; line-height:1.4; }
.header-box { border:none; border-radius:8px; padding:12px; background:#f0f5ff; margin-bottom:12px; }
.section-title { font-size:11px; font-weight:700; color:#0b2b51; margin:5px 0 0 0; }
.info-table { width:100%; border-collapse:collapse; margin-bottom:8px; }
.info-table td { padding:4px; border-bottom:1px solid #dce7f5; font-size:9px; }
.info-table .label { font-weight:600; color:#0b2b51; width:40%; }
.info-table .value { color:#4a5f7f; }
.service-table { width:100%; border-collapse:collapse; margin-bottom:8px; border:none; border-radius:6px; overflow:hidden; }
.service-table thead tr { background:#0b2b51; color:#fff; }
.service-table th { padding:10px; text-align:left; font-size:9px; font-weight:600; }
.service-table td { padding:10px; }
.totals-box { width:100%; background:#f0f5ff; border:none; border-radius:6px; padding:10px; }
.totals-row { display:flex; justify-content:space-between; padding:4px 0; font-size:9px; }
.totals-label { font-weight:600; color:#0b2b51; }
.totals-amount { text-align:right; }
.total-paid { background:#0b2b51; color:#fff; padding:8px; border-radius:4px; font-weight:700; display:flex; justify-content:space-between; margin-top:6px; }
.thank-you { background:#e8f5e9; border:none; border-radius:6px; padding:10px; margin-bottom:8px; color:#2e7d32; font-size:9px; }
.notes-box { font-size:9px; color:#4a5f7f; margin-bottom:8px; }
.notes-box ul { margin:4px 0; padding-left:16px; }
.notes-box li { margin-bottom:2px; }
.footer-row { display:flex; gap:20px; font-size:8px; color:#7a8a9f; }
</style>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
<tr>
<td width="50%" style="vertical-align:top; padding-right:8px;">
    <div class="header-box">
        {$companyLogoHtml}
        <div style="font-size:16px; font-weight:700; color:#0b2b51; margin-top:8px; margin-bottom:2px;">Your Local Connection - Mauritius</div>
        <div style="font-size:16px; font-weight:700; color:#0b2b51; margin-top:8px; margin-bottom:2px;">{$companyBusinessNameSafe}</div>
        <div style="font-size:9px; color:#4a5f7f;">{$companyBusinessAddressSafe}</div>
        <div style="font-size:9px; color:#7a8a9f;  margin-top:4px;">
            <div>{$companyPhoneSafe} | {$companyEmailSafe}</div>
            <div>VAT: {$companyVatSafe} | BRN: {$companyBrnSafe}</div>
        </div>
    </div>
</td>
<td width="50%" style="vertical-align:top; padding-left:8px; text-align:left;">
    <div style="padding:8px; background:#f0f5ff; border-radius:6px; display:inline-block;">
        <div style="font-size:9px; color:#4a5f7f; margin-bottom:4px;">Powered by</div>
        {$poweredLogoHtml}
    </div>
</td>
</tr>
</table>

<div style="margin-bottom:10px;">
    <div style="font-size:20px; font-weight:700; color:#0b2b51; margin-bottom:6px;">INVOICE</div>
    <table class="info-table">
        <tr><td class="label">Invoice Number:</td><td class="value">{$invoiceNumber}</td></tr>
        <tr><td class="label">Invoice Date:</td><td class="value">{$invoiceDate}</td></tr>
        <tr><td class="label">Booking Reference:</td><td class="value">{$bookingRef}</td></tr>
    </table>
</div>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
    <tr>
        <td width="48%" style="padding-right:6px; vertical-align:top;">
            <div class="header-box">
                <div class="section-title">BILL TO</div>
                <div style="font-weight:600; color:#0b2b51; font-size:10px; margin-bottom:4px;">{$travelerName}</div>
                <table class="info-table" style="margin:0;">
                    <tr><td class="label">Address:</td><td class="value">{$travelerAddress}</td></tr>
                    <tr><td class="label">Phone:</td><td class="value">{$travelerPhone}</td></tr>
                    <tr><td class="label">Email:</td><td class="value">{$travelerEmail}</td></tr>
                </table>
            </div>
        </td>

        <td width="52%" style="padding-left:6px; vertical-align:top;">
            <div class="header-box" style="border:none;">
                <div class="section-title">ACCOUNT DETAILS</div>
                    <div style="font-weight:600; color:#0b2b51; font-size:10px; margin-bottom:4px;"></div>
                <table class="info-table" style="margin-top:6px;">
                    <tr><td class="label">Traveller Account Type:</td><td class="value">Guest Traveller</td></tr>
                    <tr><td class="label">Account ID:</td><td class="value">{$accountId}</td></tr>
                    <tr><td class="label">Currency:</td><td class="value">USD (US Dollar)</td></tr>
                    <tr><td class="label">Payment Terms:</td><td class="value"><strong>Paid in Full</strong></td></tr>
                </table>
            </div>
        </td>
    </tr>

    <tr>
        <td colspan="2" align="center" style="padding-top:10px; padding-bottom:10px;">
            <div style="padding:8px; background:#eef4ff; border:1px solid #dce7f5; border-radius:6px; font-size:9px; color:#4a5f7f; text-align:center;">
                <strong style="color:#0b2b51;">ACCOUNT HOLDER:</strong>
                This invoice has been issued to the account holder (Privileged Traveller).
            </div>
        </td>
    </tr>
</table>

<table class="service-table">
<thead>
<tr>
  
    <th style="width:22%;">SERVICE</th>
    <th style="width:18%;">SERVICE DATES</th>
    <th style="width:20%;">DESCRIPTION</th>
     <th style="width:8%;text-align:center;">QTY</th>
    <th style="width:10%;text-align:center;">UNIT PRICE</th>
    
    <th style="width:8%;text-align:center;">TOTAL</th>
</tr>
</thead>
<tbody>
{$serviceRows}
</tbody>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
<tr>
<td width="55%" style="vertical-align:top; padding-right:6px;">
    <div class="thank-you" style="background:#e8f5e9; border-radius:8px; padding:12px; margin-bottom:8px;">
        <div style="text-align:center; font-size:11px; font-weight:700; color:#2e7d32; margin-bottom:8px;">THANK YOU!</div>
        <div style="font-size:9px; color:#2e7d32; text-align:center; line-height:1.6;">
               You will receive payment confirmation by email.<br>
            Download Voucher from your account/manage trip<br><br>
            We look forward to welcoming you to Mauritius<br>
            and wish you a wonderful stay!
        </div>
    </div>
</td>
<td width="45%" style="vertical-align:top; padding-left:80px;">
    <div class="totals-box" style="background:#fff3e0; border-radius:8px; padding:10px;">
        <div class="totals-row"><span class="totals-label">Subtotal: </span><span class="totals-amount">USD {$formattedSubtotal}</span></div>
        {$discountRow}
        <div class="totals-row"><span class="totals-label">Taxable Amount: </span><span class="totals-amount">USD {$formattedTaxableAmount}</span></div>
        <div class="totals-row"><span class="totals-label">VAT ({$vatPercent}%): </span><span class="totals-amount">USD {$formattedVatAmount}</span></div>
        <div class="totals-row"><span class="totals-label"><strong>Service Fee: </strong></span><span class="totals-amount"><strong>USD {$formattedServiceFee}</strong></span></div>
        <div class="total-paid" style="background:#0b2b51; color:#fff; padding:8px; border-radius:4px; font-weight:700; display:flex; justify-content:space-between; margin-top:6px;"><span>TOTAL PAID</span> <span>USD {$formattedTotalAmount}</span></div>
    </div>
</td>
</tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
<tr>
<td width="55%" style="padding-right:6px;">
    <div class="notes-box" style="background:#eef4ff; border:1px solid #dce7f5; border-radius:6px; padding:10px; font-size:9px; color:#4a5f7f;">
        <strong style="display:block; color:#0b2b51; margin-bottom:6px;">IMPORTANT NOTES</strong>
        <ul style="margin:4px 0; padding-left:16px;">
            <li style="margin-bottom:2px;">Please present voucher and passport when required.</li>
            <li style="margin-bottom:2px;">All services are subject to availability and terms & conditions of each service provider.</li>
            <li style="margin-bottom:2px;">For amendments or cancellations, please refer to the booking terms or contact support.</li>
        </ul>
    </div>
</td>
<td width="45%" style="padding-left:6px;">
    <div class="footer-assistance" style="background:#eef4ff; border:1px solid #dce7f5; border-radius:6px; padding:10px; font-size:9px;">
        <strong style="display:block; color:#0b2b51; margin-bottom:6px;">NEED ASSISTANCE?</strong>
        <div style="color:#4a5f7f; line-height:1.8;">
            support Ticket within your account<br>
            Office Hours: 09:00 - 17:30 <br><br>
            Office : +230 427 10 60<br>
            WhatsApp: +230 52 51 11 53 <br>
           (After hours Emergency only)<br><br>
        </div>
        <div style="margin-top:8px; padding-top:8px; border-top:1px solid #dce7f5; color:#4a5f7f; font-size:8px;">
            We are here to help you before, during and after your trip.
        </div>
    </div>
</td>
</tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:10px; padding-top:10px; border-top:1px solid #dce7f5;">
<tr>
<td width="100%" style="text-align:center; font-size:8px; color:#7a8a9f; padding:6px 0;">
    <strong style="color:#0b2b51;">LRT Mauritius LTD </strong><br>
    Your Local Connection in Mauritius<br>
    <strong style="color:#0b2b51;">Powered by</strong> <span style="color:#f7971e; font-weight:700;">HOLIDAYS.IO</span>
</td>
</tr>
</table>

HTML;

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Holidaysio');
        $pdf->SetAuthor(str_replace(['<', '>', '"', "'"], '', $traveler->name ?? 'Traveler'));
        $pdf->SetTitle('Invoice ' . $invoiceNumber);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 9);
        $pdf->writeHTML($html, true, false, true, false, '');

        $filename = 'invoice-' . $invoiceNumber . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
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
