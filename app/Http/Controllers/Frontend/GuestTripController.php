<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CartItemBuilderTrait;
use App\Models\GuestOtpToken;
use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\TransportBooking;
use App\Models\Trip;
use Illuminate\Http\Request;

class GuestTripController extends Controller
{
    use CartItemBuilderTrait;
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

            $classified = \App\Services\TripStatusService::classifyTrips($trips);
            $ongoingTrips = $classified['ongoing'];
            $pastTrips = $classified['past'];

            return view('frontend.traveler.trips', compact('trips', 'ongoingTrips', 'pastTrips', 'otp'))
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

        $transportBookings = TransportBooking::where('trip_id', $trip->id)
            ->with(['transport', 'driver'])
            ->orderBy('pickup_date', 'asc')
            ->get();

        // Prepare `service_type_display` only if the booking has a persisted `service_type`.
        foreach ($transportBookings as $tb) {
            $serviceType = trim((string) ($tb->service_type ?? ''));
            if ($serviceType !== '') {
                $translated = __('transport.form.' . $serviceType);
                $tb->service_type_display = $translated !== 'transport.form.' . $serviceType
                    ? $translated
                    : ucwords(str_replace(['_', '-'], ' ', $serviceType));
            } else {
                // For older rows without service_type, show a dash as requested
                $tb->service_type_display = '-';
            }
        }

        $allDates = [];
        foreach ($accommodationBookings as $booking) {
            if ($booking->check_in_date) $allDates[] = $booking->check_in_date;
            if ($booking->check_out_date) $allDates[] = $booking->check_out_date;
        }
        foreach ($activityBookings as $booking) {
            if ($booking->activity_date) $allDates[] = $booking->activity_date;
        }
        foreach ($transportBookings as $booking) {
            if ($booking->pickup_date) $allDates[] = $booking->pickup_date;
            if ($booking->return_date) $allDates[] = $booking->return_date;
        }

        $tripStartDate = !empty($allDates) ? min($allDates) : $trip->start_date;
        $tripEndDate = !empty($allDates) ? max($allDates) : $trip->end_date;

        return view('frontend.traveler.trip-detail', compact(
            'trip',
            'accommodationBookings',
            'activityBookings',
            'transportBookings',
            'tripStartDate',
            'tripEndDate',
            'otp'
        ))->with('guestMode', true);
    }

    public function downloadVoucher($otp, Trip $trip, $bookingId, $guestId = null, Request $request = null)
    {
        $otpToken = $this->resolveGuestToken($otp);
        if (!$otpToken || !$this->tripBelongsToGuest($trip, $otpToken)) {
            abort(403);
        }

        $this->authenticateGuest($otpToken);

        $serviceType = strtolower((string) ($request?->query('service_type') ?? ''));

        $packageLineItem = \App\Models\BookingLineItem::where('id', $bookingId)
            ->whereHas('booking', fn ($query) => $query->where('trip_id', $trip->id))
            ->with('booking')
            ->first();

        if ($packageLineItem && ($packageLineItem->service_type ?? null) === 'package') {
            $booking = null;
        } else {
            $packageLineItem = null;
            $booking = AccommodationBooking::where('id', $bookingId)
                ->where('trip_id', $trip->id)
                ->with(['accommodation', 'room', 'guests'])
                ->first();
        }

        if (!$booking) {
            $booking = ActivityBooking::where('id', $bookingId)
                ->where('trip_id', $trip->id)
                ->with(['activity.operator', 'activity.operationsStaffing', 'activity.schedulingTimeSlots', 'guests'])
                ->first();
        }

        if (!$booking) {
            $booking = TransportBooking::where('id', $bookingId)
                ->where('trip_id', $trip->id)
                ->with(['transport.operator', 'driver', 'guests'])
                ->first();
        }

        if (!$booking && $packageLineItem && $packageLineItem->service_type === 'package') {
            $package = \App\Models\Package::find($packageLineItem->service_id);
            if (!$package) {
                abort(404);
            }

            $packageItinerary = is_array($package->itinerary ?? null) ? $package->itinerary : [];
            $resolvedType = $serviceType !== '' ? $serviceType : 'accommodation';
            foreach ($packageItinerary as $entry) {
                if (!is_array($entry)) {
                    continue;
                }

                if ($resolvedType === 'accommodation' && !empty($entry['accommodation'])) {
                    $resolvedType = 'accommodation';
                    break;
                }
                if ($resolvedType === 'activity' && !empty($entry['activity'])) {
                    $resolvedType = 'activity';
                    break;
                }
                if ($resolvedType === 'transport' && !empty($entry['transport'])) {
                    $resolvedType = 'transport';
                    break;
                }
            }

            $serviceEntry = null;
            foreach ($packageItinerary as $entry) {
                if (!is_array($entry)) {
                    continue;
                }

                if ($serviceType !== '' && $serviceType === 'activity' && !empty($entry['activity'])) {
                    $serviceEntry = $entry;
                    break;
                }
                if ($serviceType !== '' && $serviceType === 'transport' && !empty($entry['transport'])) {
                    $serviceEntry = $entry;
                    break;
                }
                if ($serviceType !== '' && $serviceType === 'accommodation' && !empty($entry['accommodation'])) {
                    $serviceEntry = $entry;
                    break;
                }
                if ($serviceType === '' && !empty($entry['accommodation'])) {
                    $serviceEntry = $entry;
                    break;
                }
            }

            if ($serviceType === 'activity' && $serviceEntry && !empty($serviceEntry['activity'])) {
                $activity = \App\Models\Activity::find((int) $serviceEntry['activity']);
                if ($activity) {
                    $booking = new ActivityBooking([
                        'id' => $packageLineItem->id,
                        'trip_id' => $trip->id,
                        'booking_reference' => 'PACKAGE-' . $packageLineItem->id,
                        'booking_status' => $packageLineItem->status ?? 'Pending',
                        'total_amount' => $packageLineItem->price ?? 0,
                        'currency' => $packageLineItem->currency ?? 'USD',
                        'activity_date' => $trip->start_date ? \Carbon\Carbon::parse($trip->start_date) : \Carbon\Carbon::today(),
                        'adults' => max(1, $packageLineItem->travellers()->count()),
                        'children' => 0,
                        'variant_name' => $activity->activity_name ?? 'Package Activity',
                    ]);
                    $booking->setRelation('activity', $activity);
                    $booking->setRelation('guests', $packageLineItem->travellers()->get());
                }
            } elseif ($serviceType === 'transport' && $serviceEntry && !empty($serviceEntry['transport'])) {
                $transport = \App\Models\Transport::with('routes')->find((int) $serviceEntry['transport']);
                if ($transport) {
                    $booking = new TransportBooking([
                        'id' => $packageLineItem->id,
                        'trip_id' => $trip->id,
                        'booking_reference' => 'PACKAGE-' . $packageLineItem->id,
                        'booking_status' => $packageLineItem->status ?? 'Pending',
                        'total_amount' => $packageLineItem->price ?? 0,
                        'currency' => $packageLineItem->currency ?? 'USD',
                        'pickup_date' => $trip->start_date ? \Carbon\Carbon::parse($trip->start_date) : \Carbon\Carbon::today(),
                        'return_date' => $trip->start_date ? \Carbon\Carbon::parse($trip->start_date)->addDay() : \Carbon\Carbon::today()->addDay(),
                        'route_from' => $transport->route_from ?? null,
                        'route_to' => $transport->route_to ?? null,
                        'pickup_time' => $transport->pickup_time ?? null,
                        'return_time' => $transport->return_time ?? null,
                    ]);
                    $booking->setRelation('transport', $transport);
                    $booking->setRelation('guests', $packageLineItem->travellers()->get());
                }
            } elseif ($serviceType === 'accommodation' || $serviceType === '') {
                $accommodationEntry = null;
                foreach ($packageItinerary as $entry) {
                    if (is_array($entry) && !empty($entry['accommodation'])) {
                        $accommodationEntry = $entry;
                        break;
                    }
                }

                if ($accommodationEntry && !empty($accommodationEntry['accommodation'])) {
                    $accommodation = \App\Models\Accommodation::with('rooms')->find((int) $accommodationEntry['accommodation']);
                    if ($accommodation) {
                        $booking = new AccommodationBooking([
                            'id' => $packageLineItem->id,
                            'trip_id' => $trip->id,
                            'booking_reference' => 'PACKAGE-' . $packageLineItem->id,
                            'booking_status' => $packageLineItem->status ?? 'Pending',
                            'total_amount' => $packageLineItem->price ?? 0,
                            'currency' => $packageLineItem->currency ?? 'USD',
                            'check_in_date' => $trip->start_date ? \Carbon\Carbon::parse($trip->start_date) : \Carbon\Carbon::today(),
                            'check_out_date' => $trip->start_date ? \Carbon\Carbon::parse($trip->start_date)->addDay() : \Carbon\Carbon::today()->addDay(),
                            'adults' => max(1, $packageLineItem->travellers()->count()),
                            'children' => 0,
                        ]);
                        $booking->setRelation('accommodation', $accommodation);
                        $booking->setRelation('room', $accommodation->rooms->first());
                        $booking->setRelation('guests', $packageLineItem->travellers()->get());
                    }
                }
            }
        }

        if (!$booking) {
            abort(404);
        }

        $isActivity = $booking instanceof ActivityBooking;
        $isAccommodation = $booking instanceof AccommodationBooking;
        $isTransport = $booking instanceof TransportBooking;

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
        } elseif ($isTransport) {
            $transport = $booking->transport;
            if (!$transport) {
                abort(404);
            }
            $operator = $transport->operator;
            $ops = null;
        } else {
            abort(404);
        }

        $company = $this->getAdminCompanyData();
        $companyBusinessNameSafe = e($company['business_name']);
        $companyBusinessAddressSafe = e($company['business_address']);
        $companyEmailSafe = e($company['business_email']);
        $companyPhoneSafe = e($company['business_phone']);
        $companyVatSafe = e($company['vat_number']);
        $companyBrnSafe = e($company['brn_number']);
        $companyLogoHtml = $this->renderAdminCompanyLogoHtml($company['logo_path'], $company['business_name']);

        $voucherDate = $isActivity
            ? optional($booking->activity_date)->format('d/m/Y')
            : ($isTransport
                ? trim((optional($booking->pickup_date)->format('d/m/Y') ?: '') . ' - ' . (optional($booking->return_date)->format('d/m/Y') ?: ''))
                : (optional($booking->check_in_date)->format('d/m/Y') . ' - ' . optional($booking->check_out_date)->format('d/m/Y')));
        $serviceName = $isActivity
            ? ($activity->activity_name ?? 'Activity')
            : ($isTransport
                ? ($transport->vehicle_name ?? 'Transport')
                : ($accommodation->property_name ?? 'Accommodation'));
        $variantName = $isActivity
            ? ($booking->variant_name ? 'Variant: ' . $booking->variant_name : 'Standard option')
            : ($isTransport
                ? ($transport->vehicle_name ? 'Vehicle: ' . $transport->vehicle_name : 'Standard transport option')
                : ($room ? 'Room: ' . $room->room_name : 'Standard room'));
        $duration = $isActivity ? ($activity->duration ? 'Duration: ' . $activity->duration : '') : '';
        $allowedTags = '<strong><em><u><br><p><ul><ol><li><b><i>';

        $poweredLogoPath = public_path('images/holidays-io-logo-poweredby2.png');
        if (!file_exists($poweredLogoPath)) {
            $poweredLogoPath = '';
        } elseif (preg_match('/\.png$/i', $poweredLogoPath)) {
            $poweredLogoPath = $this->getSanitizedPngForTcpdf($poweredLogoPath);
        }

        $poweredLogoHtml = $poweredLogoPath
            ? '<img src="' . $poweredLogoPath . '" width="70" style="width:70px; height:auto; display:block;" alt="Holidays.io logo">'
            : '<div style="font-size:18px;font-weight:700;color:#f7971e;">Holidays.io</div>';

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
            if ($activity->reservation_contact_phone) {
                $reservationContact[] = 'Phone: ' . $activity->reservation_contact_phone;
            }
            if ($activity->reservation_contact_mobile) {
                $reservationContact[] = 'Mobile: ' . $activity->reservation_contact_mobile;
            }

            $accountingContact = [];
            if ($activity->accounting_contact_phone) {
                $accountingContact[] = 'Phone: ' . $activity->accounting_contact_phone;
            }
            if ($activity->accounting_contact_mobile) {
                $accountingContact[] = 'Mobile: ' . $activity->accounting_contact_mobile;
            }

            $managementContact = [];
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
            if ($accommodation->reservation_contact_phone) {
                $reservationContact[] = 'Phone: ' . $accommodation->reservation_contact_phone;
            }
            if ($accommodation->reservation_contact_mobile) {
                $reservationContact[] = 'Mobile: ' . $accommodation->reservation_contact_mobile;
            }

            $accountingContact = [];
            if ($accommodation->accounting_contact_phone) {
                $accountingContact[] = 'Phone: ' . $accommodation->accounting_contact_phone;
            }
            if ($accommodation->accounting_contact_mobile) {
                $accountingContact[] = 'Mobile: ' . $accommodation->accounting_contact_mobile;
            }

            $managementContact = [];
            if ($accommodation->management_contact_phone) {
                $managementContact[] = 'Phone: ' . $accommodation->management_contact_phone;
            }
            if ($accommodation->management_contact_mobile) {
                $managementContact[] = 'Mobile: ' . $accommodation->management_contact_mobile;
            }

            $opsContact = [];
        } elseif ($isTransport) {
            $reservationContact = [];
            if (!empty($operator->phone)) {
                $reservationContact[] = 'Phone: ' . $operator->phone;
            }
            if (!empty($operator->mobile)) {
                $reservationContact[] = 'Mobile: ' . $operator->mobile;
            }
            if (!empty($operator->reservation_contact_phone)) {
                $reservationContact[] = 'Reservation: ' . $operator->reservation_contact_phone;
            }
            if (!empty($operator->reservation_contact_mobile)) {
                $reservationContact[] = 'Reservation: ' . $operator->reservation_contact_mobile;
            }

            $accountingContact = [];
            $managementContact = [];
            $opsContact = [];
        }

        $bookingGuests = $booking->guests ?? collect();
        $voucherGuest = null;
        if ($guestId && $bookingGuests->count()) {
            $voucherGuest = $bookingGuests->firstWhere('id', $guestId);
            if (!$voucherGuest) {
                abort(404);
            }
        }

        $guestsForVoucher = $voucherGuest
            ? [$voucherGuest]
            : ($bookingGuests->count()
                ? $bookingGuests->all()
                : [
                    (object) [
                        'first_name' => trim(($booking->traveler_first_name ?? '') . ' ' . ($booking->traveler_middle_name ?? '')) ?: ($booking->guest_name ?? 'Guest'),
                        'last_name' => $booking->traveler_last_name ?? '',
                        'nationality' => $booking->traveler_nationality ?? '-',
                        'dob' => $booking->traveler_dob ?? null,
                        'phone' => $booking->guest_phone ?? null,
                        'email' => $otpToken->email ?? $booking->guest_email ?? null,
                        'guest_number' => 1,
                    ],
                ]);
        
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

        $issueDate = now()->format('d/m/Y');
        if ($isActivity) {
            $serviceDate = optional($booking->activity_date)->format('d/m/Y');
            $checkInDisplay = optional($booking->activity_date)->format('d/m/Y') . ' • ' . ($booking->activity_time ?? '-');
            $checkOutDisplay = $activity->duration ? e($activity->duration) : '-';
        } elseif ($isTransport) {
            $serviceDate = trim((optional($booking->pickup_date)->format('d/m/Y') ?: '') . ' - ' . (optional($booking->return_date)->format('d/m/Y') ?: '')) ?: 'N/A';
            $checkInDisplay = optional($booking->pickup_date)->format('d/m/Y') . ' • ' . ($booking->pickup_time ?? '-');
            $checkOutDisplay = optional($booking->return_date)->format('d/m/Y') . ' • ' . ($booking->return_time ?? '-');
        } else {
            $serviceDate = trim((optional($booking->check_in_date)->format('d/m/Y') ?: '') . ' - ' . (optional($booking->check_out_date)->format('d/m/Y') ?: '')) ?: 'N/A';
            $checkInDisplay = optional($booking->check_in_date)->format('d/m/Y') . ' • From ' . ($booking->check_in_time ?? '14:00');
            $checkOutDisplay = optional($booking->check_out_date)->format('d/m/Y') . ' • By ' . ($booking->check_out_time ?? '11:00');
        }
        $nights = '-';
        if ($isAccommodation && $booking->check_in_date && $booking->check_out_date) {
            $diff = $booking->check_in_date->diffInDays($booking->check_out_date);
            $nights = $diff . ' Night' . ($diff === 1 ? '' : 's');
        }

        $firstGuestGuest = isset($guestsForVoucher[0]) ? $guestsForVoucher[0] : null;
        $bookingCustomerName = trim((string) ($booking->guest_name ?? ''));
        $responsibleName = $bookingCustomerName !== ''
            ? $bookingCustomerName
            : (($firstGuestGuest ? trim(($firstGuestGuest->first_name ?? '') . ' ' . ($firstGuestGuest->last_name ?? '')) : null) ?: '-');
        $responsibleMobile = $booking->guest_mobile ?? $booking->guest_phone ?? ($firstGuestGuest->phone ?? '-') ?? '-';
        $responsibleEmail = $otpToken->email ?? $booking->guest_email ?? ($firstGuestGuest->email ?? '-') ?? '-';
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
        $comparisonName = trim((string) $responsibleName);
        $otherTravellerNames = array_values(array_filter(array_map(function ($name) {
            return trim((string) $name);
        }, $guestNames), function ($name) use ($comparisonName) {
            return $name !== '' && $name !== $comparisonName;
        }));
        $otherTravellers = !empty($otherTravellerNames) ? e(implode(', ', $otherTravellerNames)) : '-';

        $providerName = $accommodation->property_name ?? ($activity->activity_name ?? ($transport->vehicle_name ?? 'Service Provider'));
        if ($isActivity) {
            if (is_numeric($activity->regions)) {
                $regionModel = \App\Models\Region::find((int) $activity->regions);
                $activityRegionValue = $regionModel?->name ?? $activity->address ?? null;
            } else {
                $activityRegionValue = $activity->regions ?? $activity->address ?? null;
            }
            $providerAddress = trim(implode(', ', array_filter([
                $activity->destination ?? null,
                $activity->town ?? null,
                $activityRegionValue ?? null,
                $operator->country ?? null,
            ])), ', ');
            $locationLabel = $activity->town ?: ($activityRegionValue ?: ($operator->country ?? 'Mauritius'));
            $emergencyContact = $activity->emergency_contact_phone ?? $operator->emergency_contact_phone ?? null;
            $receptionContact = $activity->reception_contact_phone ?? $operator->reception_contact_phone ?? null;
        } elseif ($isTransport) {
            $providerAddress = trim(implode(', ', array_filter([
                $operator->address_line_1 ?? null,
                $operator->address_line_2 ?? null,
                $operator->city ?? null,
                $operator->country ?? null,
            ])), ', ');
            $locationLabel = $booking->route_to ?: ($operator->country ?? 'Mauritius');
            $emergencyContact = $operator->emergency_contact_phone ?? $operator->phone ?? null;
            $receptionContact = $operator->reception_contact_phone ?? $operator->mobile ?? null;
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
        $contactDisplay = function ($contactValue) {
            if ($contactValue === null || $contactValue === '') {
                return '-';
            }

            $parts = is_array($contactValue) ? $contactValue : preg_split('/\s*\|\s*/', (string) $contactValue);
            $filtered = [];
            foreach ((array) $parts as $part) {
                $part = trim((string) $part);
                if ($part === '') {
                    continue;
                }
                if (preg_match('/^(Name|Email):/i', $part)) {
                    continue;
                }
                $filtered[] = $part;
            }

            return !empty($filtered) ? implode(' | ', $filtered) : '-';
        };

        if (!$emergencyContact && !empty($reservationContact)) {
            $emergencyContact = $reservationContact;
        }
        if (!$receptionContact && !empty($reservationContact)) {
            $receptionContact = $reservationContact;
        }
        $providerAddress = $providerAddress ?: '-';
        $emergencyContact = $contactDisplay($emergencyContact);
        $receptionContact = $contactDisplay($receptionContact);

        $roomType = $isTransport ? ($transport->vehicle_name ?? '-') : ($room->room_name ?? $booking->room_name ?? '-');
        $occupancy = $isTransport
            ? (($booking->total_passengers !== null ? (int) $booking->total_passengers . ' Passengers' : ((($booking->adults ?? 0) + ($booking->children ?? 0)) . ' Passengers')))
            : ($adultCount !== null ? (int) $adultCount . ' Adults' . ($childCount ? ' • ' . (int) $childCount . ' Children' : '') : '-');
        $mealPlan = $isTransport
            ? (($booking->route_from || $booking->route_to) ? trim(($booking->route_from ?? '') . ' → ' . ($booking->route_to ?? '')) : 'N/A')
            : ($booking->meal_plan ?? $booking->package_name ?? 'N/A');
        $pickupAddress = $booking->pickup_address ?? null;
        $dropoffAddress = $booking->dropoff_address ?? null;
        $specialRequests = $booking->special_request ?? $booking->special_requests ?? 'None';
        $bookingNotes = $booking->notes ?? $booking->booking_notes ?? '-';

        $transportDetailRow = '';
        if ($isTransport) {
            $transportDetailRow = '<tr>' .
                '<td class="label">Pickup Address</td>' .
                '<td><strong>' . e($pickupAddress ?: '-') . '</strong></td>' .
                '</tr>' .
                '<tr>' .
                '<td class="label">Drop-off Address</td>' .
                '<td><strong>' . e($dropoffAddress ?: '-') . '</strong></td>' .
                '</tr>';
        }

        $operatorLabel = e($operator->business_name ?? $providerName);
        $locationLabelSafe = e('Mauritius');
        $voucherTitle = e($isTransport ? 'Transport Service Voucher' : ($isActivity ? 'Activity Service Voucher' : 'Accommodation Service Voucher'));
        $bookingReferenceSafe = e($booking->booking_reference ?? '-');
        $issueDateSafe = e($issueDate);
        $serviceDateSafe = e($serviceDate);
        $serviceTypeLabel = e($isTransport ? 'Transport' : ($isActivity ? 'Activity' : 'Accommodation'));
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
        $dropoffAddressSafe = e($dropoffAddress ?: '-');
        $serviceTypeDisplaySafe = '';
        if ($isTransport) {
            if (!empty($booking->service_type_display)) {
                $transportServiceTypeDisplay = $booking->service_type_display;
            } else {
                $transportServiceTypeValue = trim((string) ($booking->service_type ?? ''));
                if ($transportServiceTypeValue !== '') {
                    $translatedServiceType = __('transport.form.' . $transportServiceTypeValue, [], app()->getLocale());
                    $transportServiceTypeDisplay = $translatedServiceType !== 'transport.form.' . $transportServiceTypeValue
                        ? $translatedServiceType
                        : ucwords(str_replace(['_', '-'], ' ', $transportServiceTypeValue));
                } else {
                    $transportServiceTypeDisplay = __('cart.type.transport');
                }
            }
            $serviceTypeDisplaySafe = e($transportServiceTypeDisplay);
        }
        $specialRequestsSafe = e($specialRequests);
        $bookingNotesSafe = e($bookingNotes);
        $infoLabelCheckIn = e($isTransport ? 'Pickup Date / Time' : ($isActivity ? 'Activity Date / Time' : 'Check-in Date / Time'));
        $infoLabelCheckOut = e($isTransport ? 'Return Date / Time' : ($isActivity ? 'Finish / Duration' : 'Check-out Date / Time'));
        $infoLabelDaysNights = e($isTransport ? 'Route' : ($isActivity ? 'Number of Days' : 'Number of Nights'));
        $infoLabelType = e($isTransport ? 'Vehicle Type' : ($isActivity ? 'Activity Type' : 'Room Type'));
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
    <div style="padding:16px;text-align:left;min-height:90px;">
        {$companyLogoHtml}
         <div style="font-size:12px;font-weight:700;color:#0b2b51;margin-top:6px;">Your Local Connection - Mauritius</div>
        <div style="font-size:12px;font-weight:700;color:#0b2b51;margin-top:8px;">{$companyBusinessNameSafe}</div>
        <div style="font-size:10px;color:#6a7b91;margin-top:6px;">{$companyBusinessAddressSafe}</div>
        <div style="font-size:10px;color:#6a7b91;margin-top:4px;">{$companyPhoneSafe} | {$companyEmailSafe}</div>
    </div>
</td>
<td width="35%" style="text-align:left;vertical-align:top;">
    <div style="display:inline-block;padding:10px 12px;background:#ffffff;">
        <div style="font-size:11px;color:#5f6d7a;margin-bottom:6px;">Powered by</div>
        {$poweredLogoHtml}
    </div>
</td>
</tr>
</table>

<h1 style="font-size:22px;margin:12px 0 4px 0;color:#0b2b51;">{$voucherTitle}</h1>
<div style="font-size:10px;color:#5f6d7a;margin-bottom:16px;">Voucher No: {$bookingReferenceSafe} | Issue Date: {$issueDateSafe} |  Status: <span style="color:#28a745;font-weight:bold;">Confirmed</span></div>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
    <tr>
        <td width="50%" style="padding:4px;">
            <div class="info-card">
                <strong>Booking Ref.</strong><br>
                {$bookingReferenceSafe}
            </div>
        </td>

        <td width="50%" style="padding:4px;">
            <div class="info-card">
                <strong>Service Date</strong><br>
                {$serviceDateSafe}
            </div>
        </td>
    </tr>
</table>

<div  style="margin-bottom:20px;">
    <div class="section-title" style="margin-bottom:12px;">Responsible Traveller</div>
    <table width="100%" class="info-row" cellpadding="6" cellspacing="0">
        <tr>
            <td width="50%" style="padding-bottom:8px;"><div class="label">Full Name</div><div class="value" style="margin-top:4px;">{$responsibleNameSafe}</div></td>
            <td width="50%" style="padding-bottom:8px;"><div class="label">Other Travellers</div><div class="value" style="margin-top:4px;">{$otherTravellersSafe}</div></td>
        </tr>
    </table>
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:8px;">
        <tr><td><div class="small-text" style="margin:0;"><strong>Travel Party Size:</strong> {$travelPartySizeSafe}</div></td></tr>
    </table>
</div>
<table width="100%" cellpadding="0" cellspacing="0">
<tr>

<td width="50%" style="padding:8px;vertical-align:top;">
    <div class="box" style="background:#f8fbff; border:1px solid #11335e; border-radius:2px; padding:10px;">
        <div class="section-title" style="font-size:12px; font-weight:bold; color:#0b2b51; margin-bottom:8px; border-bottom:1px solid #dce7f5; padding-bottom:5px;">
            Service Details
        </div>

        <table width="100%" class="info-row" cellpadding="4" cellspacing="0">
            <tr>
                <td class="label" width="40%"><strong>Property Name</strong></td>
                <td><strong style="color:#0b2b51;">{$serviceNameSafe}</strong></td>
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
                <td class="label">Service Type</td>
                <td>{$serviceTypeDisplaySafe}</td>
            </tr>

            <tr>
                <td class="label">Occupancy</td>
                <td>{$occupancySafe}</td>
            </tr>

            <tr>
                <td class="label">Meal Plan</td>
                <td>{$mealPlanSafe}</td>
            </tr>

            {$transportDetailRow}

            <tr>
                <td class="label">Special Requests</td>
                <td>{$specialRequestsSafe}</td>
            </tr>

            <tr>
                <td class="label">Booking Notes</td>
                <td>{$bookingNotesSafe}</td>
            </tr>
        </table>
    </div>
</td>
<td width="50%" style="padding-right:8px;vertical-align:top;">
    <div class="box">
        <div class="section-title">Service Provider / Property</div>

        <div class="small-text" style="font-size:8px;">
            {$providerNameSafe}<br>
            {$providerAddressSafe}
        </div>

      

        <div style="margin-top:6px; font-size:8px;">
            Reception / Service Contact<br>
            {$receptionContactSafe}
        </div>
          <div style="margin-top:10px; font-size:8px;">
            Emergency Contact (24/7)<br>
            {$emergencyContactSafe}
        </div>
    </div>
</td>
</tr>
</table>

<div class="box">
    <div class="section-title">Important Information / Conditions</div>
    <ul class="check-list" style="font-size:8px; line-height:1.3; margin:0; padding-left:15px;">
        <li>Please present this voucher on arrival at the property.</li>
        <li>All travellers must carry a valid passport or national ID.</li>
        <li>Early check-in / late check-out are subject to availability and at a servicee fee.</li>
        <li>All amendments and cancellations are subject to the property's booking conditions.</li>
        <li>For any assistance during your stay, contact the MPO using the details below.</li>
    </ul><div style="border:1px solid #d7e4f0;background:#eef4fb;border-radius:10px;padding:10px;margin-top:10px;" class="small-text">
        <strong>Your Local Connection</strong> – We are here to support you. For enquiries or assistance during your trip, please contact us.
    </div>
</div>

<div class="footer-box">
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td width="60%" style="vertical-align:top;">
    <div style="font-size:13px;font-weight:700;color:#0b2b51;">MPO Support and Emergency</div>
   
        <div style="color:#4a5f7f; line-height:1.5;">
            support Ticket within your account<br>
            Office Hours: 09:00 - 17:30 <br><br>
            Office : +230 427 10 60<br>
            WhatsApp: +230 52 51 11 53 <br>
            (After hours Emergency only)<br><br>
        </div>
        <div style="color:#4a5f7f; font-size:8px;">
            We are here to help you before, during and after your trip.
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
</div>
HTML;

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
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
           // $pdf->write2DBarcode($voucherUrl ?? '', 'QRCODE,H', $x, $y, 35, 35, [], 'N');
        }
        $filename = ($isActivity ? 'activity' : ($isTransport ? 'transport' : 'accommodation')) . '-voucher-' . preg_replace('/[^A-Za-z0-9_-]/', '', $booking->booking_reference) . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    protected function getGuestTripIds(GuestOtpToken $otpToken)
    {
        $accommodationTripIds = AccommodationBooking::where('guest_email', $otpToken->email)
            ->pluck('trip_id')
            ->filter()
            ->unique()
            ->toArray();

        $activityTripIds = ActivityBooking::where('guest_email', $otpToken->email)
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
                $query->where('guest_email', $otpToken->email);
            })
            ->orWhere('guest_otp_token_id', $otpToken->id)
            ->with(['accommodation', 'room', 'guests'])
            ->orderBy('check_in_date', 'asc')
            ->get();

        $activityBookings = ActivityBooking::where(function ($query) use ($otpToken) {
                $query->where('guest_email', $otpToken->email);
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
                          $subQuery->where('guest_email', $otpToken->email);
                      });
            })
            ->get();

        $activityBookings = ActivityBooking::whereNull('trip_id')
            ->where(function ($query) use ($otpToken) {
                $query->where('guest_otp_token_id', $otpToken->id)
                      ->orWhere(function ($subQuery) use ($otpToken) {
                          $subQuery->where('guest_email', $otpToken->email);
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
                ->first();
            $tripId = $booking?->trip_id;
        }

        if (!$tripId) {
            $booking = ActivityBooking::where('guest_email', $otpToken->email)
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

    public function manageGuests($otp, Trip $trip, $bookingId, Request $request = null)
    {
        $otpToken = $this->resolveGuestToken($otp);
        if (!$otpToken || !$this->tripBelongsToGuest($trip, $otpToken)) {
            abort(403);
        }

        $this->authenticateGuest($otpToken);

        $serviceType = strtolower((string) ($request?->query('service_type') ?? ''));

        $packageLineItem = \App\Models\BookingLineItem::where('id', $bookingId)
            ->whereHas('booking', fn ($query) => $query->where('trip_id', $trip->id))
            ->with('booking')
            ->first();

        if ($packageLineItem && ($packageLineItem->service_type ?? null) === 'package') {
            $booking = null;
        } else {
            $packageLineItem = null;
            $booking = $this->findBookingForTrip($trip, $bookingId);
        }

        if (!$booking && $packageLineItem && $packageLineItem->service_type === 'package') {
            $package = \App\Models\Package::find($packageLineItem->service_id);
            $travellerCollection = $packageLineItem->travellers()->get();
            $guestCollection = $travellerCollection->map(function ($traveller) {
                $fullName = trim((string) ($traveller->name ?? ''));
                $parts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);

                return (object) [
                    'first_name' => $parts[0] ?? '',
                    'middle_name' => '',
                    'last_name' => implode(' ', array_slice($parts, 1)),
                    'dob' => $traveller->date_of_birth ? $traveller->date_of_birth->format('Y-m-d') : null,
                    'gender' => null,
                    'nationality' => null,
                    'passport_number' => null,
                    'notes' => null,
                    'guest_number' => 1,
                ];
            })->values();

            if ($serviceType === 'activity' && $package && !empty($package->itinerary)) {
                $activityId = null;
                foreach ($package->itinerary as $entry) {
                    if (is_array($entry) && !empty($entry['activity'])) {
                        $activityId = (int) $entry['activity'];
                        break;
                    }
                }

                $activity = $activityId ? \App\Models\Activity::find($activityId) : null;
                if ($activity) {
                    $booking = new ActivityBooking([
                        'id' => $packageLineItem->id,
                        'trip_id' => $trip->id,
                        'booking_reference' => 'PACKAGE-' . $packageLineItem->id,
                        'booking_status' => $packageLineItem->status ?? 'Pending',
                        'total_amount' => $packageLineItem->price ?? 0,
                        'currency' => $packageLineItem->currency ?? 'USD',
                        'adults' => max(1, $travellerCollection->count()),
                        'children' => 0,
                        'variant_name' => $activity->activity_name ?? 'Package Activity',
                        'activity_date' => $trip->start_date ? \Carbon\Carbon::parse($trip->start_date) : \Carbon\Carbon::today(),
                    ]);
                    $booking->setRelation('activity', $activity);
                    $booking->setRelation('guests', $guestCollection);
                }
            } elseif ($serviceType === 'accommodation' && $package && !empty($package->itinerary)) {
                $accommodationId = null;
                foreach ($package->itinerary as $entry) {
                    if (is_array($entry) && !empty($entry['accommodation'])) {
                        $accommodationId = (int) $entry['accommodation'];
                        break;
                    }
                }

                $accommodation = $accommodationId ? \App\Models\Accommodation::with('rooms')->find($accommodationId) : null;
                if ($accommodation) {
                    $booking = new AccommodationBooking([
                        'id' => $packageLineItem->id,
                        'trip_id' => $trip->id,
                        'booking_reference' => 'PACKAGE-' . $packageLineItem->id,
                        'booking_status' => $packageLineItem->status ?? 'Pending',
                        'total_amount' => $packageLineItem->price ?? 0,
                        'currency' => $packageLineItem->currency ?? 'USD',
                        'adults' => max(1, $travellerCollection->count()),
                        'children' => 0,
                        'check_in_date' => $trip->start_date ? \Carbon\Carbon::parse($trip->start_date) : \Carbon\Carbon::today(),
                        'check_out_date' => $trip->start_date ? \Carbon\Carbon::parse($trip->start_date)->addDay() : \Carbon\Carbon::today()->addDay(),
                    ]);
                    $booking->setRelation('accommodation', $accommodation);
                    $booking->setRelation('room', $accommodation->rooms->first());
                    $booking->setRelation('guests', $guestCollection);
                }
            } elseif ($serviceType === 'transport' && $package && !empty($package->itinerary)) {
                $transportId = null;
                foreach ($package->itinerary as $entry) {
                    if (is_array($entry) && !empty($entry['transport'])) {
                        $transportId = (int) $entry['transport'];
                        break;
                    }
                }

                $transport = $transportId ? \App\Models\Transport::with('routes')->find($transportId) : null;
                if ($transport) {
                    $booking = new TransportBooking([
                        'id' => $packageLineItem->id,
                        'trip_id' => $trip->id,
                        'booking_reference' => 'PACKAGE-' . $packageLineItem->id,
                        'booking_status' => $packageLineItem->status ?? 'Pending',
                        'total_amount' => $packageLineItem->price ?? 0,
                        'currency' => $packageLineItem->currency ?? 'USD',
                        'pickup_date' => $trip->start_date ? \Carbon\Carbon::parse($trip->start_date) : \Carbon\Carbon::today(),
                        'return_date' => $trip->start_date ? \Carbon\Carbon::parse($trip->start_date)->addDay() : \Carbon\Carbon::today()->addDay(),
                        'route_from' => $transport->route_from ?? null,
                        'route_to' => $transport->route_to ?? null,
                        'pickup_time' => $transport->pickup_time ?? null,
                        'return_time' => $transport->return_time ?? null,
                    ]);
                    $booking->setRelation('transport', $transport);
                    $booking->setRelation('guests', $guestCollection);
                }
            }
        }

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

    public function downloadInvoice($otp, Trip $trip)
    {
        $otpToken = $this->resolveGuestToken($otp);
        if (!$otpToken || !$this->tripBelongsToGuest($trip, $otpToken)) {
            abort(403);
        }

        // Get all bookings for the trip
        $accommodationBookings = $trip->accommodationBookings ?? collect();
        $activityBookings = $trip->activityBookings ?? collect();
        $transportBookings = $trip->transportBookings()->with(['transport'])->get() ?? collect();
        
        $allBookings = $accommodationBookings->merge($activityBookings)->merge($transportBookings);
        
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
        $invoiceDate = now()->format('d/m/Y');
        $bookingRef = 'B' . str_pad($trip->id, 4, '0', STR_PAD_LEFT);

        // Guest details - safe escaping
        $checkoutGuestName = '';
        foreach ($allBookings as $bookingItem) {
            $candidate = trim((string) data_get($bookingItem, 'guest_name', ''));
            if ($candidate !== '') {
                $checkoutGuestName = $candidate;
                break;
            }
        }
        $travelerName = e($checkoutGuestName !== '' ? $checkoutGuestName : ($otpToken->name ?? 'Guest Traveller'));
        $travelerPhone = e($otpToken->phone ?? $otpToken->mobile ?? $otpToken->mobile_phone ?? $otpToken->phone_number ?? $otpToken->contact_number ?? $otpToken->contact_phone ?? 'N/A');
        $travelerEmail = e($otpToken->email ?? 'N/A');
        $travelerAddress = e($otpToken->address ?? 'Address not provided');
        $accountId = 'GUEST-' . str_pad($trip->id, 6, '0', STR_PAD_LEFT);

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
                // The $amount is already the total price for the entire stay, not per-night
                $unitPrice = $nights > 0 ? $amount / $nights : $amount;
                $totalForLine = $amount;

                $mealPlanLabel = $booking->meal_plan ? e($booking->meal_plan) : 'N/A';
                $rateNameLabel = $booking->rate_name ? e($booking->rate_name) : '';
                $pricingSettingLabel = $booking->pricing_setting ? e($booking->pricing_setting) : '';
                $planInclusions = null;
                if (!empty($booking->plan_inclusions)) {
                    $decodedInclusions = json_decode($booking->plan_inclusions, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedInclusions)) {
                        $planInclusions = implode(', ', $decodedInclusions);
                    } else {
                        $planInclusions = (string) $booking->plan_inclusions;
                    }
                }

                $descriptionParts = [
                    $booking->room->room_name ?? 'Room',
                ];
                if ($rateNameLabel !== '') {
                    $descriptionParts[] = $rateNameLabel;
                }
                if ($booking->meal_plan) {
                    $descriptionParts[] = $mealPlanLabel;
                }

                $notesParts = [
                    'Room: ' . ($booking->room->room_name ?? 'Standard'),
                ];
                if ($pricingSettingLabel !== '') {
                    $notesParts[] = 'Pricing: ' . $pricingSettingLabel;
                }
               // $notesParts[] = 'Meal Plan: ' . $mealPlanLabel;
                if ($planInclusions) {
                    $notesParts[] = 'Includes: ' . $planInclusions;
                }

                $item = [
                    'type' => 'Accommodation',
                    'name' => e($booking->accommodation->property_name ?? 'Accommodation'),
                    'location' => e($booking->accommodation->city ?? 'Mauritius'),
                    'checkIn' => $booking->check_in_date ? $booking->check_in_date->format('d/m/Y') : 'N/A',
                    'checkOut' => $booking->check_out_date ? $booking->check_out_date->format('d/m/Y') : 'N/A',
                    'description' => e(implode(' - ', array_filter($descriptionParts))),
                    'notes' => e(implode(' | ', $notesParts)),
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
                    'checkIn' => $booking->activity_date ? $booking->activity_date->format('d/m/Y') : 'N/A',
                    'checkOut' => $booking->activity_date ? $booking->activity_date->format('d/m/Y') : 'N/A',
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

        foreach ($transportBookings as $booking) {
            $amount = (float) data_get($booking, 'total_amount', $booking->total_price ?? 0);
            if ($booking->transport && $amount > 0) {
                $passengers = max(1, (int) data_get($booking, 'total_passengers', $booking->adults + ($booking->children ?? 0)));
                $pricePerPerson = (float) data_get($booking, 'price_per_person', 0);
                $unitPrice = $pricePerPerson > 0 ? $pricePerPerson : $amount;
                $quantity = $pricePerPerson > 0 ? $passengers : 1;
                if (!empty($booking->service_type_display) && $booking->service_type_display !== '-') {
                    $transportServiceTypeDisplay = $booking->service_type_display;
                } else {
                    $transportServiceTypeValue = trim((string) data_get($booking, 'service_type', ''));
                    $transportServiceTypeDisplay = '';
                    if ($transportServiceTypeValue !== '') {
                        $translatedServiceType = __('transport.form.' . $transportServiceTypeValue, [], app()->getLocale());
                        $transportServiceTypeDisplay = $translatedServiceType !== 'transport.form.' . $transportServiceTypeValue
                            ? $translatedServiceType
                            : ucwords(str_replace(['_', '-'], ' ', $transportServiceTypeValue));
                    } else {
                        // older rows with no service_type: show a dash
                        $transportServiceTypeDisplay = '-';
                    }
                }
                $description = sprintf(
                    'Service Type: %s | Route: %s to %s | %s pax',
                    e($transportServiceTypeDisplay),
                    e($booking->route_from ?? 'N/A'),
                    e($booking->route_to ?? 'N/A'),
                    $passengers
                );
                $item = [
                    'type' => 'Transport',
                    'name' => e($booking->transport->vehicle_name ?? 'Transport'),
                    'location' => e($booking->route_to ?? $booking->transport->location ?? 'Mauritius'),
                    'checkIn' => $booking->pickup_date ? $booking->pickup_date->format('d/m/Y') : 'N/A',
                    'checkOut' => $booking->return_date ? $booking->return_date->format('d/m/Y') : 'N/A',
                    'description' => $description,
                    'notes' => e(($booking->transport->vehicle_type ?? 'Vehicle') . ' | Pickup: ' . ($booking->pickup_time ?? '-') . ' | Return: ' . ($booking->return_time ?? '-') . ' | Drop-off: ' . ($booking->dropoff_address ?? '-')),
                    'qty' => $quantity,
                    'unitPrice' => $unitPrice,
                    'total' => $amount,
                ];
                $invoiceItems[] = $item;
                $subtotal += $amount;
            }
        }

        // Calculate totals and cart-style breakdown for display
        $discountPercent = 0;
        $discountAmount = 0;
        $taxCharges = 0;
        $fees = 0;
        $taxableAmount = $subtotal;
        $vatPercent = 0;
        $vatAmount = 0;
        $totalAmount = $subtotal;

        foreach ($accommodationBookings as $booking) {
            if (!$booking->accommodation) {
                continue;
            }
            $amount = (float) data_get($booking, 'total_amount', $booking->total_price ?? 0);
            $nights = $booking->check_in_date && $booking->check_out_date
                ? (int) $booking->check_out_date->diffInDays($booking->check_in_date)
                : 1;
            $nights = max(1, abs($nights));
            $adults = isset($booking->adults) ? max(1, (int) $booking->adults) : max(1, $booking->guests->count());
            $taxCharges += $this->calcAccommodationTax($booking->accommodation, $amount, $adults, $nights);
            $fees += $this->calcAccommodationFees($booking->accommodation, $booking->room_id ?? null, $nights);
        }

        foreach ($activityBookings as $booking) {
            if (!$booking->activity) {
                continue;
            }
            $amount = (float) data_get($booking, 'total_amount', $booking->total_price ?? 0);
            $participants = isset($booking->adults) ? max(1, (int) $booking->adults) : max(1, $booking->guests->count());
            $taxCharges += $this->calcActivityTax($booking->activity, $amount, $participants);
        }

        foreach ($transportBookings as $booking) {
            if (!$booking->transport) {
                continue;
            }
            $amount = (float) data_get($booking, 'total_amount', $booking->total_price ?? 0);
            if ($amount <= 0) {
                continue;
            }
            $taxCharges += $this->calcTransportTax($booking->transport, $amount);
        }

        $totalAmount = $subtotal;

        $formattedSubtotal = number_format($subtotal, 2);
        $formattedDiscountAmount = number_format($discountAmount, 2);
        $formattedTaxableAmount = number_format($taxableAmount, 2);
        $formattedVatAmount = number_format($vatAmount, 2);
        $formattedServiceFee = number_format($fees, 2);
        $formattedTaxCharges = number_format($taxCharges, 2);
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

$taxableRow = '';
if ($taxableAmount > 0 && $taxableAmount != $subtotal) {
    $taxableRow = '<div class="totals-row"><span class="totals-label">Taxable Amount: </span><span class="totals-amount">USD ' . $formattedTaxableAmount . '</span></div>';
}

$vatRow = '';
if ($vatAmount > 0) {
    $vatRow = '<div class="totals-row"><span class="totals-label">VAT (' . $vatPercent . '%): </span><span class="totals-amount">USD ' . $formattedVatAmount . '</span></div>';
}

$taxChargesRow = '';
if ($taxCharges > 0) {
    $taxChargesRow = '<div class="totals-row"><span class="totals-label">Taxes & Charges: </span><span class="totals-amount">USD ' . $formattedTaxCharges . '</span></div>';
}

$feesRow = '';
if ($fees > 0) {
    $feesRow = '<div class="totals-row"><span class="totals-label">Fees: </span><span class="totals-amount">USD ' . $formattedServiceFee . '</span></div>';
}
$mealPlanValues = $accommodationBookings
    ->pluck('meal_plan')
    ->filter()
    ->unique()
    ->values()
    ->all();
$mealPlanSafe = !empty($mealPlanValues)
    ? e(implode(', ', $mealPlanValues))
    : '-';
        $invoiceTitle = e(__('invoice.title'));
        $invoiceNumberLabel = e(__('invoice.number_label'));
        $invoiceDateLabel = e(__('invoice.date_label'));
        $bookingRefLabel = e(__('invoice.booking_ref'));
        $billToLabel = e(__('invoice.bill_to'));
        $accountDetailsLabel = e(__('invoice.account_details'));
        $addressLabel = e(__('invoice.address_label'));
        $phoneLabel = e(__('invoice.phone_label'));
        $emailLabel = e(__('invoice.email_label'));
        $mealPlanLabel = e(__('invoice.meal_plan'));
        $travellerAccountTypeLabel = e(__('invoice.traveller_account_type'));
        $accountIdLabel = e(__('invoice.account_id'));
        $currencyLabel = e(__('invoice.currency'));
        $paymentTermsLabel = e(__('invoice.payment_terms'));
        $accountHolderLabel = e(__('invoice.account_holder_label'));
        $accountHolderNote = e(__('invoice.account_holder_note'));
        $serviceLabel = e(__('invoice.service'));
        $serviceDatesLabel = e(__('invoice.service_dates'));
        $descriptionLabel = e(__('invoice.description'));
        $qtyLabel = e(__('invoice.qty'));
        $unitLabel = e(__('invoice.unit'));
        $unitPriceLabel = e(__('invoice.unit_price'));
        $totalLabel = e(__('invoice.total'));
        $html = <<<HTML
<style>
body { font-family:helvetica; color:#222; font-size:10px; line-height:1.4; }
.header-box { border:none; border-radius:8px; padding:12px; background:#f0f5ff; margin-bottom:12px; }
.section-title { font-size:11px; font-weight:700; color:#0b2b51; margin:8px 0 6px 0; }
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
    <div style="font-size:20px; font-weight:700; color:#0b2b51; margin-bottom:6px;">{$invoiceTitle}</div>
    <table class="info-table">
        <tr><td class="label">{$invoiceNumberLabel}</td><td class="value">{$invoiceNumber}</td></tr>
        <tr><td class="label">{$invoiceDateLabel}</td><td class="value">{$invoiceDate}</td></tr>
        <tr><td class="label">{$bookingRefLabel}</td><td class="value">{$bookingRef}</td></tr>
    </table>
</div>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
    <tr>
        <td width="48%" style="padding-right:6px; vertical-align:top;">
            <div class="header-box">
                <div class="section-title">{$billToLabel}</div>
                <div style="font-weight:600; color:#0b2b51; font-size:10px; margin-bottom:4px;">{$travelerName}</div>
                <table class="info-table" style="margin:0;">
                    <tr><td class="label">{$addressLabel}</td><td class="value">{$travelerAddress}</td></tr>
                    <tr><td class="label">{$phoneLabel}</td><td class="value">{$travelerPhone}</td></tr>
                    <tr><td class="label">{$emailLabel}</td><td class="value">{$travelerEmail}</td></tr>
                    <tr><td class="label">{$mealPlanLabel}</td><td class="value">{$mealPlanSafe}</td></tr>
                </table>
            </div>
        </td>

        <td width="52%" style="padding-left:6px; vertical-align:top;">
            <div class="header-box" style="border:none;">
                <div class="section-title">{$accountDetailsLabel}</div>
                                <div style="font-weight:600; color:#0b2b51; font-size:10px; margin-bottom:4px;"></div>
                <table class="info-table" style="margin-top:6px;margin-bottom:4px;">
                    <tr><td class="label">{$travellerAccountTypeLabel}</td><td class="value">Guest Traveller</td></tr>
                    <tr><td class="label">{$accountIdLabel}</td><td class="value">{$accountId}</td></tr>
                    <tr><td class="label">{$currencyLabel}</td><td class="value">USD (US Dollar)</td></tr>
                    <tr><td class="label">{$paymentTermsLabel}</td><td class="value"><strong>Paid in Full</strong></td></tr>
                </table>
            </div>
        </td>
    </tr>

    <tr>
        <td colspan="2" align="center" style="padding-top:10px; padding-bottom:10px;">
            <div style="padding:8px; background:#eef4ff; border:1px solid #dce7f5; border-radius:6px; font-size:9px; color:#4a5f7f; text-align:center;">
                <strong style="color:#0b2b51;">{$accountHolderLabel}</strong>
                {$accountHolderNote}
            </div>
        </td>
    </tr>
</table>
<table class="service-table">
<thead>
<tr>
    
    <th style="width:22%;">{$serviceLabel}</th>
    <th style="width:18%;">{$serviceDatesLabel}</th>
    <th style="width:20%;">{$descriptionLabel}</th>
    <th style="width:8%;text-align:center;">{$qtyLabel}</th>
    <th style="width:10%;text-align:center;">{$unitPriceLabel}</th>
    <th style="width:8%;text-align:center;">{$totalLabel}</th>
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
        {$taxChargesRow}
        {$feesRow}
        {$taxableRow}
        {$vatRow}
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
        $pdf->SetAuthor(str_replace(['<', '>', '"', "'"], '', $otpToken->name ?? 'Guest'));
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
