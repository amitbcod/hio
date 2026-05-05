<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\AccommodationFee;
use App\Models\AccommodationPromotion;
use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\ActivityPromotion;
use App\Models\BookingGuest;
use App\Models\SavedGuest;
use App\Models\TravelerCart;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\BookingLineItem;
use App\Models\Traveller;
use App\Models\BliTravellerAllocation;
use App\Models\GuestOtpToken;
use App\Services\TripService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════════
    //  ADD TO CART
    // ═══════════════════════════════════════════════════════════════════════

    public function addToCart(Request $request)
    {
        $type = $request->input('type'); // 'accommodation' | 'activity'

        $cart = $this->resolveCart();

        if ($type === 'accommodation') {
            $item = $this->buildAccommodationCartItem($request);
        } elseif ($type === 'activity') {
            $item = $this->buildActivityCartItem($request);
        } else {
            return back()->with('error', 'Invalid booking type.');
        }

        $cart[$item['cart_key']] = $item;
        $this->storeCart($cart);

        return redirect()->route('frontend.booking.cart')
            ->with('success', 'Item added. Please proceed to checkout.');
    }

    private function buildAccommodationCartItem(Request $request): array
    {
        $accommodationId = (int) $request->input('accommodation_id');
        $roomId          = $request->input('room_id') ? (int) $request->input('room_id') : null;
        $checkIn         = $request->input('check_in');
        $checkOut        = $request->input('check_out');
        $adults          = max(1, (int) $request->input('adults', 2));
        $children        = max(0, (int) $request->input('children', 0));
        $infants         = max(0, (int) $request->input('infants', 0));
        $nightlyPrice    = (float) $request->input('nightly_price', 0);
        $totalPrice      = (float) $request->input('total_price', 0);
        $currency        = $request->input('currency', 'USD');
        $roomName        = $request->input('room_name', 'Standard Room');
        $rooms           = max(1, (int) $request->input('rooms', 1));
        $nights          = max(1, (int) $request->input('nights', 1));
        $image           = $request->input('image', '');
        $title           = $request->input('title', '');
        $pricingSetting  = $request->input('pricing_setting', 'Per Room/Night');
        $planLabel       = $request->input('plan_label', '');

        // Load accommodation for tax/fee details
        $accommodation = Accommodation::find($accommodationId);

        $taxAmount    = 0.0;
        $feeAmount    = 0.0;
        $discountAmount = 0.0;
        $promotionId  = null;
        $isNonRefundable = false;

        if ($accommodation) {
            // Tax calculation
            $taxAmount = $this->calcAccommodationTax($accommodation, $totalPrice, $adults, $nights);

            // Fees
            $feeAmount = $this->calcAccommodationFees($accommodation, $roomId, $nights);

            // Active promotion
            $promo = AccommodationPromotion::where('accommodation_id', $accommodationId)
                ->when($roomId, fn($q) => $q->where(function($q2) use ($roomId) {
                    $q2->whereNull('room_id')->orWhere('room_id', $roomId);
                }))
                ->active()
                ->orderByDesc('discount_value')
                ->first();

            if ($promo) {
                $promotionId     = $promo->id;
                $isNonRefundable = (bool) $promo->non_refundable;
                $discountAmount  = $this->calcPromoDiscount(
                    $promo->discount_type,
                    $promo->discount_value,
                    $totalPrice,
                    $nights
                );
            }
        }

        $priceAfterDiscount = max(0, $totalPrice - $discountAmount);
        $netAmount          = $priceAfterDiscount + $taxAmount + $feeAmount;

        return [
            'cart_key'         => uniqid('accom_', true),
            'type'             => 'accommodation',
            'accommodation_id' => $accommodationId,
            'room_id'          => $roomId,
            'room_name'        => $roomName,
            'title'            => $title ?: ($accommodation->property_name ?? 'Accommodation'),
            'image'            => $image,
            'check_in'         => $checkIn,
            'check_out'        => $checkOut,
            'check_in_display' => $checkIn  ? Carbon::parse($checkIn)->format('d M Y')  : '',
            'check_out_display'=> $checkOut ? Carbon::parse($checkOut)->format('d M Y') : '',
            'nights'           => $nights,
            'adults'           => $adults,
            'children'         => $children,
            'infants'          => $infants,
            'nightly_price'    => $nightlyPrice,
            'total_price'      => $totalPrice,
            'currency'         => $currency,
            'discount_amount'  => $discountAmount,
            'tax_amount'       => $taxAmount,
            'fee_amount'       => $feeAmount,
            'net_amount'       => $netAmount,
            'rooms'            => $rooms,
            'promotion_id'     => $promotionId,
            'is_non_refundable'=> $isNonRefundable,
            'pricing_setting'  => $pricingSetting,
            'plan_label'       => $planLabel,
        ];
    }

    private function buildActivityCartItem(Request $request): array
    {
        $activityId  = (int) $request->input('activity_id');
        $variantId   = $request->input('variant_id') ? (int) $request->input('variant_id') : null;
        $variantName = $request->input('variant_name', '');
        $checkIn     = $request->input('check_in') ?: $request->input('activity_date');   // activity date
        $checkOut    = $request->input('check_out') ?: $checkIn;
        $participants = $request->has('participants')
            ? max(1, (int) $request->input('participants'))
            : max(1, (int) $request->input('adults', 1));
        $adults      = $participants;
        $children    = max(0, (int) $request->input('children', 0));
        $totalPrice  = (float) $request->input('total_price', 0);
        $currency    = $request->input('currency', 'USD');
        $image       = $request->input('image', '');
        $title       = $request->input('title', '');

        $activity     = Activity::with(['accounting'])->find($activityId);
        $taxAmount    = 0.0;
        $discountAmount = 0.0;
        $promotionId  = null;
        $isNonRefundable = false;

        if ($activity) {
            $taxAmount = $this->calcActivityTax($activity, $totalPrice, $adults);

            $promo = ActivityPromotion::where('activity_id', $activityId)
                ->where('approval_status', 'Published')
                ->where(function($q) {
                    $q->whereNull('promo_valid_from')
                      ->orWhere('promo_valid_from', '<=', now()->toDateString());
                })
                ->where(function($q) {
                    $q->whereNull('promo_valid_to')
                      ->orWhere('promo_valid_to', '>=', now()->toDateString());
                })
                ->orderByDesc('discount_value')
                ->first();

            if ($promo) {
                $appliesToVariant = empty($promo->variant_ids) || in_array($variantId, (array) $promo->variant_ids);
                if ($appliesToVariant) {
                    $promotionId     = $promo->promotion_id;
                    $isNonRefundable = ($promo->non_refundable === 'Yes');
                    $discountAmount  = $this->calcPromoDiscount(
                        $promo->discount_type,
                        $promo->discount_value,
                        $totalPrice,
                        1
                    );
                }
            }
        }

        $priceAfterDiscount = max(0, $totalPrice - $discountAmount);
        $netAmount          = $priceAfterDiscount + $taxAmount;

        return [
            'cart_key'         => uniqid('actv_', true),
            'type'             => 'activity',
            'activity_id'      => $activityId,
            'variant_id'       => $variantId,
            'variant_name'     => $variantName,
            'title'            => $title ?: ($activity->activity_name ?? 'Activity'),
            'image'            => $image,
            'check_in'         => $checkIn,
            'check_out'        => $checkIn,  // same day for activities
            'check_in_display' => $checkIn ? Carbon::parse($checkIn)->format('d M Y') : '',
            'check_out_display'=> $checkIn ? Carbon::parse($checkIn)->format('d M Y') : '',
            'nights'           => 1,
            'adults'           => $adults,
            'children'         => $children,
            'participants'     => $participants,
            'nightly_price'    => $totalPrice,
            'total_price'      => $totalPrice,
            'currency'         => $currency,
            'discount_amount'  => $discountAmount,
            'tax_amount'       => $taxAmount,
            'fee_amount'       => 0.0,
            'net_amount'       => $netAmount,
            'promotion_id'     => $promotionId,
            'is_non_refundable'=> $isNonRefundable,
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  VIEW CART (Review Page)
    // ═══════════════════════════════════════════════════════════════════════

    public function viewCart()
    {
        $cart = $this->resolveCart();

        if (empty($cart)) {
            return redirect()->route('frontend.home')
                ->with('error', 'No booking in progress. Please search and select a property first.');
        }

        $summary = $this->buildCartSummary($cart);

        return view('frontend.cart-review', compact('cart', 'summary'));
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  REMOVE FROM CART
    // ═══════════════════════════════════════════════════════════════════════

    public function removeFromCart(Request $request)
    {
        $cartKey = $request->input('cart_key');
        $cart    = $this->resolveCart();

        unset($cart[$cartKey]);
        $this->storeCart($cart);

        return back()->with('success', 'Item removed from cart.');
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GUEST CHECKOUT (No auth required - simple form)
    // ═══════════════════════════════════════════════════════════════════════

    public function guestCheckout()
    {
        $cart = $this->resolveCart();

        if (empty($cart)) {
            return redirect()->route('frontend.home')->with('error', 'Your booking is empty.');
        }

        $summary = $this->buildCartSummary($cart);

        $totalGuests = 0;
        foreach ($cart as $item) {
            $totalGuests += $item['adults'] + $item['children'] + ($item['infants'] ?? 0);
        }

        $guestDefaults = [
            'guest_name' => old('guest_name') ?: '',
            'guest_email' => old('guest_email') ?: '',
            'guest_phone' => old('guest_phone') ?: '',
            'dob' => old('dob') ?: '',
        ];

        // Load time slots for activities in cart
        $activityTimeSlots = [];
        foreach ($cart as $item) {
            if ($item['type'] === 'activity' && !empty($item['activity_id'])) {
                $activity = Activity::with('schedulingTimeSlots')->find($item['activity_id']);
                if ($activity) {
                    $slots = $activity->schedulingTimeSlots
                        ->map(fn($slot) => [
                            'id' => $slot->timeslot_id,
                            'start_time' => $slot->start_time,
                            'end_time' => $slot->end_time,
                            'duration' => $slot->duration,
                            'display' => $slot->start_time . ' - ' . $slot->end_time . ' (' . $slot->duration . ')',
                        ])
                        ->values()
                        ->toArray();
                    $activityTimeSlots[$item['activity_id']] = $slots;
                }
            }
        }

        $countries = $this->countries();
        $savedGuests = collect(); // No saved guests for guests
        $traveler = null;
        $travelerProfile = null;

        return view('frontend.checkout', compact('cart', 'summary', 'guestDefaults', 'traveler', 'travelerProfile', 'countries', 'totalGuests', 'savedGuests', 'activityTimeSlots'));
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  CHECKOUT (Guest info form - requires auth)
    // ═══════════════════════════════════════════════════════════════════════

    public function checkout()
    {
        if (!Auth::guard('traveler')->check()) {
            return redirect()->route('traveler.login')->with('error', 'Please log in as a traveler to continue to checkout.');
        }

        $cart = $this->resolveCart();

        if (empty($cart)) {
            return redirect()->route('frontend.home')->with('error', 'Your booking is empty.');
        }

        $summary = $this->buildCartSummary($cart);

        $totalGuests = 0;
        foreach ($cart as $item) {
            $totalGuests += $item['adults'] + $item['children'] + ($item['infants'] ?? 0);
        }

        $traveler = Auth::guard('traveler')->user();
        $travelerProfile = $traveler?->profile;

        $savedGuests = $traveler ? SavedGuest::where('user_id', $traveler->id)->get() : collect();

        $travelerDOB = $travelerProfile?->date_of_birth ? $travelerProfile->date_of_birth->format('Y-m-d') : null;

        if ($traveler) {
            $selfFirstName = $travelerProfile?->first_name ?: null;
            $selfLastName = $travelerProfile?->last_name ?: null;

            if (!$selfFirstName && $traveler?->full_name) {
                $nameParts = preg_split('/\s+/', trim($traveler->full_name), 2);
                $selfFirstName = $nameParts[0] ?? null;
                $selfLastName = $nameParts[1] ?? null;
            }

            $selfExists = $savedGuests->contains(function ($guest) use ($selfFirstName, $selfLastName, $travelerDOB) {
                return trim((string) $guest->first_name) === trim((string) $selfFirstName)
                    && trim((string) $guest->last_name) === trim((string) $selfLastName)
                    && optional($guest->dob)?->format('Y-m-d') === $travelerDOB;
            });

            if (!$selfExists) {
                $selfGuest = new SavedGuest();
                $selfGuest->id = 'self';
                $selfGuest->relation = 'self';
                $selfGuest->gender = $travelerProfile?->gender;
                $selfGuest->first_name = $selfFirstName;
                $selfGuest->middle_name = $travelerProfile?->middle_name;
                $selfGuest->last_name = $selfLastName;
                $selfGuest->dob = $travelerDOB;
                $selfGuest->nationality = $travelerProfile?->nationality;
                $selfGuest->passport_number = $travelerProfile?->passport_number;
                $selfGuest->notes = 'This is your traveler profile.';

                $savedGuests->prepend($selfGuest);
            }
        }

        $guestDefaults = [
            'guest_name' => old('guest_name') ?: ($traveler?->full_name ?: ($travelerProfile ? trim($travelerProfile->first_name . ' ' . ($travelerProfile->middle_name ?? '') . ' ' . $travelerProfile->last_name) : null)),
            'guest_email' => old('guest_email') ?: ($traveler?->email ?? null),
            'guest_phone' => old('guest_phone') ?: ($traveler?->mobile_phone ?? null),
            'dob' => $travelerDOB,
        ];

        // Load time slots for activities in cart
        $activityTimeSlots = [];
        foreach ($cart as $item) {
            if ($item['type'] === 'activity' && !empty($item['activity_id'])) {
                $activity = Activity::with('schedulingTimeSlots')->find($item['activity_id']);
                if ($activity) {
                    $slots = $activity->schedulingTimeSlots
                        ->map(fn($slot) => [
                            'id' => $slot->timeslot_id,
                            'start_time' => $slot->start_time,
                            'end_time' => $slot->end_time,
                            'duration' => $slot->duration,
                            'display' => $slot->start_time . ' - ' . $slot->end_time . ' (' . $slot->duration . ')',
                        ])
                        ->values()
                        ->toArray();
                    $activityTimeSlots[$item['activity_id']] = $slots;
                }
            }
        }

        $countries = $this->countries();

        return view('frontend.checkout', compact('cart', 'summary', 'guestDefaults', 'traveler', 'travelerProfile', 'countries', 'totalGuests', 'savedGuests', 'activityTimeSlots'));
    }

    private function countries(): array
    {
        return [
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
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  PLACE ORDER (COD)
    // ═══════════════════════════════════════════════════════════════════════

    public function placeOrder(Request $request)
    {
        $cart = $this->resolveCart();
        if (empty($cart)) {
            return redirect()->route('frontend.home')->with('error', 'Your booking is empty.');
        }

        $totalGuests = 0;
        foreach ($cart as $item) {
            $totalGuests += $item['adults'] + $item['children'] + ($item['infants'] ?? 0);
        }

        $guestsInput = $request->input('guests', []);
        $guestEmail = $request->input('guest_email');
        $guestPhone = $request->input('guest_phone');
        $special    = $request->input('special_requests', '');

        // Determine if this is a guest or authenticated checkout
        $isGuestCheckout = !Auth::guard('traveler')->check();
        $travelerAccount = Auth::guard('traveler')->user();
        
        // Validate email for guest checkout
        if ($isGuestCheckout) {
            if (empty($guestEmail)) {
                return back()->with('error', 'Email is required for guest checkout.');
            }
            if (!filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
                return back()->with('error', 'Please enter a valid email address.');
            }
        }

        // Parse participant time slots JSON
        $participantTimeSlotsJson = $request->input('participant_time_slots_json', '{}');
        $participantTimeSlots = [];
        try {
            $participantTimeSlots = json_decode($participantTimeSlotsJson, true) ?? [];
        } catch (\Exception $e) {
            $participantTimeSlots = [];
        }

        $primaryGuests = isset($guestsInput[0]) ? [$guestsInput[0]] : [];
        $primaryGuests = collect($primaryGuests)
            ->filter(function ($guest) {
                return is_array($guest) && (
                    !empty(trim($guest['first_name'] ?? '')) ||
                    !empty(trim($guest['last_name'] ?? '')) ||
                    !empty(trim($guest['dob'] ?? ''))
                );
            })
            ->values()
            ->all();

        // Extract global additional guests (indices 1,2,3...)
        $globalAdditionalGuests = [];
        $i = 1;
        while (isset($guestsInput[$i])) {
            $globalAdditionalGuests[] = $guestsInput[$i];
            $i++;
        }
        $globalAdditionalGuests = collect($globalAdditionalGuests)
            ->filter(function ($guest) {
                return is_array($guest) && (
                    !empty(trim($guest['first_name'] ?? '')) ||
                    !empty(trim($guest['last_name'] ?? '')) ||
                    !empty(trim($guest['dob'] ?? ''))
                );
            })
            ->values()
            ->all();

        $allAdditionalGuests = [];
        foreach ($cart as $item) {
            $itemGuestsForValidation = isset($guestsInput[$item['cart_key']]) ? collect($guestsInput[$item['cart_key']])
                ->filter(function ($guest) {
                    return is_array($guest) && (
                        !empty(trim($guest['first_name'] ?? '')) ||
                        !empty(trim($guest['last_name'] ?? '')) ||
                        !empty(trim($guest['dob'] ?? ''))
                    );
                })
                ->values()
                ->all() : [];

            $allAdditionalGuests = array_merge($allAdditionalGuests, $itemGuestsForValidation);
        }

        $allAdditionalGuests = array_merge($allAdditionalGuests, $globalAdditionalGuests);

        // Only validate guest details if they are provided (optional for guest checkout)
        if (!empty($allAdditionalGuests)) {
            Validator::make(['guests' => $allAdditionalGuests], [
                'guests' => 'array',
                'guests.*.relation' => 'required|in:self,spouse,child,friend,colleague,other',
                'guests.*.first_name' => 'required|string|max:100',
                'guests.*.middle_name' => 'nullable|string|max:100',
                'guests.*.last_name' => 'required|string|max:100',
                'guests.*.dob' => 'required|date|before:today',
                'guests.*.gender' => 'nullable|in:male,female,non_binary,other,Mr,Mrs,Miss,Ms,Mx,Other',
                'guests.*.nationality' => 'required|string|max:100',
                'guests.*.passport_number' => 'nullable|string|max:100',
                'guests.*.notes' => 'nullable|string|max:1000',
            ])->validate();
        }

        if (!empty($primaryGuests)) {
            Validator::make(['guests' => $primaryGuests], [
                'guests' => 'array',
                'guests.*.relation' => 'required|in:self,spouse,child,friend,colleague,other',
                'guests.*.first_name' => 'required|string|max:100',
                'guests.*.middle_name' => 'nullable|string|max:100',
                'guests.*.last_name' => 'required|string|max:100',
                'guests.*.dob' => 'required|date|before:today',
                'guests.*.gender' => 'nullable|in:male,female,non_binary,other,Mr,Mrs,Miss,Ms,Mx,Other',
                'guests.*.nationality' => 'required|string|max:100',
                'guests.*.passport_number' => 'nullable|string|max:100',
                'guests.*.notes' => 'nullable|string|max:1000',
            ])->validate();
        }
        $guestEmail = $request->input('guest_email');
        $guestPhone = $request->input('guest_phone');
        $special    = $request->input('special_requests', '');

        // Use the first item's currency / grand total
        $summary = $this->buildCartSummary($cart);
        $bookingRefs = [];
        $guestOtp = null;

        // Get or create Trip ID
        $travelerAccount = Auth::guard('traveler')->user();
        $tripData = [];

        foreach ($cart as $item) {
            if ($item['type'] === 'accommodation') {
                $tripData['start_date'] = $item['check_in'];
                $tripData['end_date'] = $item['check_out'];
            } elseif ($item['type'] === 'activity') {
                $tripData['start_date'] = $item['check_in'];
            }
            $tripData['title'] = $tripData['title'] ?? ucfirst($item['type']) . ' Trip';
        }

        // Get or create Trip ID - check for explicit trip selection first
        $tripId = null;
        $explicitTripId = session('add_to_trip_id');
        
        if ($explicitTripId) {
            $tripId = $explicitTripId;
            session()->forget('add_to_trip_id');
        } elseif ($travelerAccount) {
            $tripId = TripService::getOrCreateTripId($travelerAccount, $tripData);
        } elseif ($isGuestCheckout) {
            // Create trip for guest booking
            $trip = \App\Models\Trip::create([
                'traveler_account_id' => null,
                'title' => $tripData['title'] ?? 'Guest Trip',
                'start_date' => $tripData['start_date'] ?? null,
                'end_date' => $tripData['end_date'] ?? null,
                'status' => 'planned',
            ]);
            $tripId = $trip->id;
        }

        foreach ($cart as $item) {
            $dateForRef = $item['check_in'] ?? now()->format('Y-m-d');
            $ref = $this->generateBookingRef($item['type'], $tripId, $dateForRef);

            $travelerAccountId = Auth::guard('traveler')->id() ?? null;
            $primaryGuest = $primaryGuests[0] ?? [];

            $itemGuests = isset($guestsInput[$item['cart_key']]) ? collect($guestsInput[$item['cart_key']])
                ->filter(function ($guest) {
                    return is_array($guest) && (
                        !empty(trim($guest['first_name'] ?? '')) ||
                        !empty(trim($guest['last_name'] ?? '')) ||
                        !empty(trim($guest['dob'] ?? ''))
                    );
                })
                ->values()
                ->all() : [];

            if (empty($primaryGuest['first_name']) && $travelerAccount) {
                $primaryGuest['relation'] = $primaryGuest['relation'] ?? 'self';
                $primaryGuest['first_name'] = $travelerAccount->first_name ?? $travelerAccount->full_name ?? null;
                $primaryGuest['middle_name'] = $primaryGuest['middle_name'] ?? $travelerAccount->middle_name ?? null;
                $primaryGuest['last_name'] = $primaryGuest['last_name'] ?? $travelerAccount->last_name ?? null;
                $primaryGuest['dob'] = $primaryGuest['dob'] ?? optional($travelerAccount->profile)->date_of_birth?->format('Y-m-d');
                $primaryGuest['gender'] = $this->normalizeGender($primaryGuest['gender'] ?? null);
                $primaryGuest['nationality'] = $primaryGuest['nationality'] ?? optional($travelerAccount->profile)->country ?? null;
                $primaryGuest['passport_number'] = $primaryGuest['passport_number'] ?? null;
                $primaryGuest['notes'] = $primaryGuest['notes'] ?? null;
            }

            $guestName = trim(($primaryGuest['first_name'] ?? '') . ' ' . ($primaryGuest['middle_name'] ?? '') . ' ' . ($primaryGuest['last_name'] ?? '')) ?: ($travelerAccount?->full_name ?? $travelerAccount?->email ?? 'Guest');

            if ($item['type'] === 'accommodation') {
                $booking = AccommodationBooking::create([
                    'booking_reference' => $ref,
                    'accommodation_id'  => $item['accommodation_id'],
                    'room_id'           => $item['room_id'] ?? null,
                    'guest_name'        => $guestName,
                    'traveler_account_id' => $travelerAccountId,
                    'traveler_relation' => $primaryGuest['relation'] ?? null,
                    'traveler_first_name' => $primaryGuest['first_name'] ?? null,
                    'traveler_middle_name' => $primaryGuest['middle_name'] ?? null,
                    'traveler_last_name' => $primaryGuest['last_name'] ?? null,
                    'traveler_dob' => $primaryGuest['dob'] ?? null,
                    'traveler_gender' => $primaryGuest['gender'] ?? null,
                    'traveler_nationality' => $primaryGuest['nationality'] ?? null,
                    'traveler_passport_number' => $primaryGuest['passport_number'] ?? null,
                    'traveler_notes' => $primaryGuest['notes'] ?? null,
                    'guest_email'       => $guestEmail,
                    'check_in_date'     => $item['check_in'],
                    'check_out_date'    => $item['check_out'],
                    'adults'            => $item['adults'],
                    'children'          => $item['children'],
                    'booking_status'    => 'Pending',
                    'total_amount'      => $item['net_amount'],
                    'currency'          => $item['currency'],
                    'source_channel'    => 'Direct',
                    'booked_at'         => now(),
                    'trip_id'           => $tripId,
                    'guest_otp_token_id' => $guestOtp?->id,
                    'is_guest'          => $isGuestCheckout ? 1 : 0,
                ]);

                if ($isGuestCheckout && !$guestOtp) {
                    $guestOtp = GuestOtpToken::createForGuest($guestEmail, $booking->id);
                }

                if ($isGuestCheckout && $guestOtp) {
                    $booking->guest_otp_token_id = $guestOtp->id;
                    $booking->save();
                }

                if ($isGuestCheckout && $guestOtp && !$guestOtp->wasRecentlyCreated) {
                    // no-op, already created earlier
                }

                if ($isGuestCheckout && $guestOtp && $guestOtp->wasRecentlyCreated) {
                    // Send OTP email to guest once after first booking creation
                    try {
                        $tripUrl = url('/') . '/traveler/guest-trips/' . $guestOtp->otp_code;
                        Mail::to($guestEmail)->send(new \App\Mail\GuestBookingOtp($booking, $guestOtp, $tripUrl));
                    } catch (\Exception $e) {
                        \Log::error('Failed to send guest booking OTP email', [
                            'email' => $guestEmail,
                            'booking_id' => $booking->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Create Trip party members if Trip exists
                if ($tripId) {
                    foreach ($itemGuests as $guest) {
                        $fullName = trim(($guest['first_name'] ?? '') . ' ' . ($guest['middle_name'] ?? '') . ' ' . ($guest['last_name'] ?? ''));
                        Traveller::firstOrCreate(
                            ['trip_id' => $tripId, 'name' => $fullName],
                            [
                                'email' => $guestEmail,
                                'phone' => $guestPhone,
                                'date_of_birth' => $guest['dob'] ?? null,
                                'relationship' => $guest['relation'] ?? 'guest',
                            ]
                        );
                    }
                    // Add global additional guests to trip
                    foreach ($globalAdditionalGuests as $guest) {
                        $fullName = trim(($guest['first_name'] ?? '') . ' ' . ($guest['middle_name'] ?? '') . ' ' . ($guest['last_name'] ?? ''));
                        Traveller::firstOrCreate(
                            ['trip_id' => $tripId, 'name' => $fullName],
                            [
                                'email' => $guestEmail,
                                'phone' => $guestPhone,
                                'date_of_birth' => $guest['dob'] ?? null,
                                'relationship' => $guest['relation'] ?? 'guest',
                            ]
                        );
                    }

                    // Create Booking record
                    $tripBooking = Booking::create([
                        'trip_id' => $tripId,
                        'operator_id' => null,
                        'total_amount' => $item['net_amount'],
                        'status' => 'pending',
                    ]);

                    // Create BookingLineItem
                    $bli = BookingLineItem::create([
                        'booking_id' => $tripBooking->id,
                        'service_type' => 'accommodation',
                        'service_id' => $item['accommodation_id'],
                        'quantity' => 1,
                        'price' => $item['net_amount'],
                        'start_date' => $item['check_in'],
                        'end_date' => $item['check_out'],
                        'status' => 'active',
                    ]);

                    // Link guests to BLI
                    foreach ($itemGuests as $guest) {
                        $fullName = trim(($guest['first_name'] ?? '') . ' ' . ($guest['middle_name'] ?? '') . ' ' . ($guest['last_name'] ?? ''));
                        $traveller = Traveller::where('trip_id', $tripId)->where('name', $fullName)->first();
                        if ($traveller) {
                            BliTravellerAllocation::create([
                                'bli_id' => $bli->id,
                                'traveller_id' => $traveller->id,
                            ]);
                        }
                    }
                    // Link global additional guests to BLI
                    foreach ($globalAdditionalGuests as $guest) {
                        $fullName = trim(($guest['first_name'] ?? '') . ' ' . ($guest['middle_name'] ?? '') . ' ' . ($guest['last_name'] ?? ''));
                        $traveller = Traveller::where('trip_id', $tripId)->where('name', $fullName)->first();
                        if ($traveller) {
                            BliTravellerAllocation::create([
                                'bli_id' => $bli->id,
                                'traveller_id' => $traveller->id,
                            ]);
                        }
                    }
                }

                // Store all guests
                foreach ($itemGuests as $index => $guest) {
                    BookingGuest::create([
                        'booking_id' => $booking->id,
                        'booking_type' => 'accommodation',
                        'guest_number' => $index + 1,
                        'relation' => $guest['relation'],
                        'first_name' => $guest['first_name'],
                        'middle_name' => $guest['middle_name'],
                        'last_name' => $guest['last_name'],
                        'dob' => $guest['dob'],
                        'gender' => $this->normalizeGender($guest['gender'] ?? null),
                        'nationality' => $guest['nationality'],
                        'passport_number' => $guest['passport_number'],
                        'notes' => $guest['notes'],
                    ]);
                }
            } elseif ($item['type'] === 'activity') {
                // Build participant time slots for this activity booking
                $itemTimeSlotsMap = [];
                if (isset($participantTimeSlots[$item['cart_key']])) {
                    $itemTimeSlotsMap = $participantTimeSlots[$item['cart_key']];
                }

                $booking = ActivityBooking::create([
                    'booking_reference' => $ref,
                    'activity_id'       => $item['activity_id'],
                    'variant_id'        => $item['variant_id'] ?? null,
                    'variant_name'      => $item['variant_name'] ?? null,
                    'guest_name'        => $guestName,
                    'traveler_account_id' => $travelerAccountId,
                    'traveler_relation' => $primaryGuest['relation'] ?? null,
                    'traveler_first_name' => $primaryGuest['first_name'] ?? null,
                    'traveler_middle_name' => $primaryGuest['middle_name'] ?? null,
                    'traveler_last_name' => $primaryGuest['last_name'] ?? null,
                    'traveler_dob' => $primaryGuest['dob'] ?? null,
                    'traveler_gender' => $primaryGuest['gender'] ?? null,
                    'traveler_nationality' => $primaryGuest['nationality'] ?? null,
                    'traveler_passport_number' => $primaryGuest['passport_number'] ?? null,
                    'traveler_notes' => $primaryGuest['notes'] ?? null,
                    'guest_email'       => $guestEmail,
                    'guest_phone'       => $guestPhone,
                    'activity_date'     => $item['check_in'] ?? now()->toDateString(),
                    'adults'            => $item['adults'],
                    'children'          => $item['children'],
                    'booking_status'    => 'Pending',
                    'total_amount'      => $item['net_amount'],
                    'currency'          => $item['currency'],
                    'payment_method'    => 'COD',
                    'source_channel'    => 'Direct',
                    'special_requests'  => $special,
                    'participant_time_slots' => !empty($itemTimeSlotsMap) ? $itemTimeSlotsMap : null,
                    'booked_at'         => now(),
                    'trip_id'           => $tripId,
                    'is_guest'          => $isGuestCheckout ? 1 : 0,
                ]);

                if ($isGuestCheckout && !$guestOtp) {
                    $guestOtp = GuestOtpToken::createForGuest($guestEmail, $booking->id);
                }

                if ($isGuestCheckout && $guestOtp) {
                    $booking->guest_otp_token_id = $guestOtp->id;
                    $booking->save();
                }

                if ($isGuestCheckout && $guestOtp && $guestOtp->wasRecentlyCreated) {
                    try {
                        $tripUrl = url('/') . '/traveler/guest-trips/' . $guestOtp->otp_code;
                        Mail::to($guestEmail)->send(new \App\Mail\GuestBookingOtp($booking, $guestOtp, $tripUrl));
                    } catch (\Exception $e) {
                        \Log::error('Failed to send guest booking OTP email', [
                            'email' => $guestEmail,
                            'booking_id' => $booking->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Create Trip party members if Trip exists
                if ($tripId) {
                    foreach ($itemGuests as $guest) {
                        $fullName = trim(($guest['first_name'] ?? '') . ' ' . ($guest['middle_name'] ?? '') . ' ' . ($guest['last_name'] ?? ''));
                        Traveller::firstOrCreate(
                            ['trip_id' => $tripId, 'name' => $fullName],
                            [
                                'email' => $guestEmail,
                                'phone' => $guestPhone,
                                'date_of_birth' => $guest['dob'] ?? null,
                                'relationship' => $guest['relation'] ?? 'guest',
                            ]
                        );
                    }
                    // Add global additional guests to trip
                    foreach ($globalAdditionalGuests as $guest) {
                        $fullName = trim(($guest['first_name'] ?? '') . ' ' . ($guest['middle_name'] ?? '') . ' ' . ($guest['last_name'] ?? ''));
                        Traveller::firstOrCreate(
                            ['trip_id' => $tripId, 'name' => $fullName],
                            [
                                'email' => $guestEmail,
                                'phone' => $guestPhone,
                                'date_of_birth' => $guest['dob'] ?? null,
                                'relationship' => $guest['relation'] ?? 'guest',
                            ]
                        );
                    }

                    // Create Booking record
                    $tripBooking = Booking::create([
                        'trip_id' => $tripId,
                        'operator_id' => null,
                        'total_amount' => $item['net_amount'],
                        'status' => 'pending',
                    ]);

                    // Create BookingLineItem
                    $bliStartDate = $item['check_in'] ?? $item['activity_date'] ?? null;
                    $bli = BookingLineItem::create([
                        'booking_id' => $tripBooking->id,
                        'service_type' => 'activity',
                        'service_id' => $item['activity_id'],
                        'quantity' => $item['adults'] + $item['children'],
                        'price' => $item['net_amount'],
                        'start_date' => $bliStartDate,
                        'end_date' => $bliStartDate,
                        'status' => 'active',
                    ]);

                    // Link guests to BLI
                    foreach ($itemGuests as $guest) {
                        $fullName = trim(($guest['first_name'] ?? '') . ' ' . ($guest['middle_name'] ?? '') . ' ' . ($guest['last_name'] ?? ''));
                        $traveller = Traveller::where('trip_id', $tripId)->where('name', $fullName)->first();
                        if ($traveller) {
                            BliTravellerAllocation::create([
                                'bli_id' => $bli->id,
                                'traveller_id' => $traveller->id,
                            ]);
                        }
                    }
                    // Link global additional guests to BLI
                    foreach ($globalAdditionalGuests as $guest) {
                        $fullName = trim(($guest['first_name'] ?? '') . ' ' . ($guest['middle_name'] ?? '') . ' ' . ($guest['last_name'] ?? ''));
                        $traveller = Traveller::where('trip_id', $tripId)->where('name', $fullName)->first();
                        if ($traveller) {
                            BliTravellerAllocation::create([
                                'bli_id' => $bli->id,
                                'traveller_id' => $traveller->id,
                            ]);
                        }
                    }
                }

                // Store all guests
                foreach ($itemGuests as $index => $guest) {
                    BookingGuest::create([
                        'booking_id' => $booking->id,
                        'booking_type' => 'activity',
                        'guest_number' => $index + 1,
                        'relation' => $guest['relation'],
                        'first_name' => $guest['first_name'],
                        'middle_name' => $guest['middle_name'],
                        'last_name' => $guest['last_name'],
                        'dob' => $guest['dob'],
                        'gender' => $this->normalizeGender($guest['gender'] ?? null),
                        'nationality' => $guest['nationality'],
                        'passport_number' => $guest['passport_number'],
                        'notes' => $guest['notes'],
                    ]);
                }
            }

            $bookingRefs[] = $ref;
        }

        $this->storeCart([]);

        // Primary booking ref for confirmation page
        $primaryRef = $bookingRefs[0] ?? 'UNKNOWN';

        return redirect()->route('frontend.booking.confirmation', ['ref' => $primaryRef])
            ->with('booking_refs', $bookingRefs)
            ->with('guest_name', $guestName)
            ->with('summary', $summary);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  CONFIRMATION
    // ═══════════════════════════════════════════════════════════════════════

    public function confirmation(string $ref)
    {
        // Try to find in accommodation bookings first
        $booking = AccommodationBooking::where('booking_reference', $ref)->first();
        $type = 'accommodation';

        if (!$booking) {
            $booking = ActivityBooking::where('booking_reference', $ref)->first();
            $type = 'activity';
        }

        $bookingRefs = session()->get('booking_refs', [$ref]);
        $guestName   = session()->get('guest_name', $booking?->guest_name ?? '');
        $summary     = session()->get('summary', []);

        return view('frontend.booking-confirmation', compact('booking', 'type', 'ref', 'bookingRefs', 'guestName', 'summary'));
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    private function buildCartSummary(array $cart): array
    {
        $subtotal       = 0.0;
        $totalDiscount  = 0.0;
        $totalTax       = 0.0;
        $totalFees      = 0.0;
        $currency       = 'USD';

        foreach ($cart as $item) {
            $subtotal      += (float) ($item['total_price']    ?? 0);
            $totalDiscount += (float) ($item['discount_amount'] ?? 0);
            $totalTax      += (float) ($item['tax_amount']      ?? 0);
            $totalFees     += (float) ($item['fee_amount']      ?? 0);
            $currency       = $item['currency'] ?? $currency;
        }

        $priceAfterDiscount = max(0, $subtotal - $totalDiscount);
        $netPayable         = $priceAfterDiscount + $totalTax + $totalFees;

        return [
            'subtotal'            => $subtotal,
            'total_discount'      => $totalDiscount,
            'price_after_discount'=> $priceAfterDiscount,
            'total_tax'           => $totalTax,
            'total_fees'          => $totalFees,
            'net_payable'         => $netPayable,
            'currency'            => $currency,
            'item_count'          => count($cart),
        ];
    }

    private function calcAccommodationTax(Accommodation $accommodation, float $totalPrice, int $adults, int $nights): float
    {
        $taxType       = $accommodation->tax_type ?? 'None';
        $chargesType   = $accommodation->tax_charges_type ?? 'Per Unit';
        $valueType     = $accommodation->tax_charges_value_type ?? 'Percentage';
        $value         = (float) ($accommodation->tax_charges_value ?? 0);

        if ($taxType === 'None' || $value <= 0) {
            return 0.0;
        }

        // Determine base
        $base = match ($chargesType) {
            'Per Person' => $totalPrice,  // already per-person aggregated
            'Per Adult'  => $totalPrice,
            default      => $totalPrice,  // Per Unit = whole stay
        };

        if ($valueType === 'Percentage') {
            return round($base * $value / 100, 2);
        }

        // Amount — multiply by nights and relevant person count
        $multiplier = match ($chargesType) {
            'Per Person' => ($adults * $nights),
            'Per Adult'  => ($adults * $nights),
            default      => $nights,
        };

        return round($value * $multiplier, 2);
    }

    private function calcAccommodationFees(Accommodation $accommodation, ?int $roomId, int $nights): float
    {
        try {
            $fee = AccommodationFee::where('accommodation_id', $accommodation->id)
                ->when($roomId, fn($q) => $q->where(function($q2) use ($roomId) {
                    $q2->whereNull('room_id')->orWhere('room_id', $roomId);
                }))
                ->first();
        } catch (\Illuminate\Database\QueryException $e) {
            // Table may not exist on this environment yet; treat as no fees.
            return 0.0;
        }

        if (!$fee) {
            return 0.0;
        }

        $total = 0.0;
        $total += (float) ($fee->cleaning_fee ?? 0);
        $total += (float) ($fee->resort_fee   ?? 0) * $nights;

        return round($total, 2);
    }

    private function calcActivityTax(Activity $activity, float $totalPrice, int $adults): float
    {
        $accounting = $activity->accounting;
        if (!$accounting) {
            return 0.0;
        }

        $taxType   = $accounting->tax_type ?? 'None';
        $valueType = $accounting->tax_charges_type ?? 'Percentage';
        $value     = (float) ($accounting->tax_charges_value ?? 0);

        if ($taxType === 'None' || $value <= 0) {
            return 0.0;
        }

        if ($valueType === 'Percentage') {
            return round($totalPrice * $value / 100, 2);
        }

        // Flat amount per basis
        $basis = $accounting->tax_charges_basis ?? 'Per Person';
        $multiplier = str_contains($basis, 'Person') ? $adults : 1;

        return round($value * $multiplier, 2);
    }

    private function calcPromoDiscount(string $discountType, float $discountValue, float $totalPrice, int $nights): float
    {
        if ($discountType === 'Percentage') {
            return round($totalPrice * $discountValue / 100, 2);
        }

        if (str_contains($discountType, 'Night')) {
            // Amount/Night
            return round($discountValue * $nights, 2);
        }

        // Flat Amount
        return round(min($discountValue, $totalPrice), 2);
    }

    private function generateBookingRef(string $type, ?int $tripId = null, ?string $date = null): string
    {
        $prefix = $type === 'accommodation' ? 'ACC' : 'ACT';
        $datePart = $date ? Carbon::parse($date)->format('Ymd') : now()->format('Ymd');

        if ($tripId) {
            // Count existing bookings for this trip
            $existingCount = AccommodationBooking::where('trip_id', $tripId)->count() +
                           ActivityBooking::where('trip_id', $tripId)->count();

            $sequenceNumber = $existingCount + 1;
            $tripTag = '100' . $tripId;

            $baseRef = sprintf('%s-%s-%s-%d', $prefix, $tripTag, $datePart, $sequenceNumber);

            // Ensure uniqueness (though sequence should make it unique)
            $candidate = $baseRef;
            $suffix = 1;
            while (
                ($type === 'accommodation' && AccommodationBooking::where('booking_reference', $candidate)->exists()) ||
                ($type === 'activity' && ActivityBooking::where('booking_reference', $candidate)->exists())
            ) {
                $candidate = sprintf('%s-%02d', $baseRef, $suffix++);
            }

            return $candidate;
        }

        return $prefix . '-' . strtoupper(substr(uniqid(), -8)) . '-' . $datePart;
    }

    private function resolveCart(): array
    {
        $cart = session()->get('booking_cart', []);

        if (!empty($cart)) {
            return $cart;
        }

        $travelerId = $this->travelerAccountId();
        if (!$travelerId) {
            return [];
        }

        $storedCart = TravelerCart::where('traveler_account_id', $travelerId)->first();
        $items = is_array($storedCart?->items) ? $storedCart->items : [];

        if (!empty($items)) {
            session()->put('booking_cart', $items);
        }

        return $items;
    }

    private function storeCart(array $cart): void
    {
        if (empty($cart)) {
            session()->forget('booking_cart');
        } else {
            session()->put('booking_cart', $cart);
        }

        $travelerId = $this->travelerAccountId();
        if (!$travelerId) {
            return;
        }

        TravelerCart::updateOrCreate(
            ['traveler_account_id' => $travelerId],
            ['items' => empty($cart) ? null : $cart]
        );
    }

    private function normalizeGender(?string $gender): ?string
    {
        if (empty($gender)) {
            return null;
        }

        $normalized = strtolower(trim($gender));
        $map = [
            'mr' => 'male',
            'mrs' => 'female',
            'miss' => 'female',
            'ms' => 'female',
            'mx' => 'non_binary',
            'other' => 'other',
        ];

        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        return in_array($normalized, ['male', 'female', 'non_binary', 'other'], true) ? $normalized : null;
    }

    private function travelerAccountId(): ?int
    {
        $traveler = Auth::guard('traveler')->user();

        return $traveler ? (int) $traveler->id : null;
    }

    public function saveGuest(Request $request)
    {
        $input = $request->all();
        if (isset($input['gender']) && $input['gender'] === '') {
            $input['gender'] = null;
        }
        $request->merge($input);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:150',
            'middle_name' => 'nullable|string|max:150',
            'last_name' => 'required|string|max:150',
            'dob' => 'required|date|before_or_equal:today',
            'gender' => 'nullable|in:male,female,non_binary,other',
            'nationality' => 'required|string|max:100',
            'passport_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $traveler = Auth::guard('traveler')->user();
        if (!$traveler) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $guest = SavedGuest::updateOrCreate(
            [
                'user_id' => $traveler->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'dob' => $request->dob,
            ],
            [
                'middle_name' => $request->middle_name,
                'gender' => $request->gender,
                'nationality' => $request->nationality,
                'passport_number' => $request->passport_number,
                'notes' => $request->notes,
            ]
        );

        return response()->json(['success' => true, 'guest' => $guest]);
    }

    public function removeGuest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $traveler = Auth::guard('traveler')->user();
        if (!$traveler) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $guest = SavedGuest::where('id', $request->guest_id)->where('user_id', $traveler->id)->first();
        if ($guest) {
            $guest->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Guest not found'], 404);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GUEST ORDER SEARCH (Access via email for guest bookings)
    // ═══════════════════════════════════════════════════════════════════════

    public function guestOrderSearch(Request $request)
    {
        $email = $request->input('email');

        if (!$email) {
            return redirect()->route('frontend.home')->with('error', 'Please provide an email address.');
        }

        // Check if guest has any bookings with this email
        $accommodationBookings = AccommodationBooking::where('guest_email', $email)
            ->where('is_guest', 1)
            ->get();

        $activityBookings = ActivityBooking::where('guest_email', $email)
            ->where('is_guest', 1)
            ->get();

        if ($accommodationBookings->isEmpty() && $activityBookings->isEmpty()) {
            return back()->with('error', 'No guest bookings found for this email. Please check and try again.');
        }

        // Show guest bookings list (without requiring OTP verification yet)
        return view('frontend.guest-order-list', compact('email', 'accommodationBookings', 'activityBookings'));
    }

    public function sendGuestOrderOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');

        // Find any booking with this email
        $booking = AccommodationBooking::where('guest_email', $email)
            ->where('is_guest', 1)
            ->first();

        if (!$booking) {
            $booking = ActivityBooking::where('guest_email', $email)
                ->where('is_guest', 1)
                ->first();
        }

        if (!$booking) {
            return back()->with('error', 'No guest bookings found for this email.');
        }

        // Create or get existing OTP token
        $guestOtp = GuestOtpToken::where('email', $email)
            ->where('expires_at', '>', now())
            ->first();

        if (!$guestOtp) {
            $guestOtp = GuestOtpToken::createForGuest($email, $booking->id);
        }

        // Send OTP email
        try {
            $tripUrl = url('/') . '/traveler/guest-trips/' . $guestOtp->otp_code;
            
            // Get accommodation or activity for email
            $accommodationName = 'Your Booking';
            if ($booking instanceof AccommodationBooking && $booking->accommodation) {
                $accommodationName = $booking->accommodation->property_name;
            } elseif ($booking instanceof ActivityBooking && $booking->activity) {
                $accommodationName = $booking->activity->activity_name;
            }

            Mail::to($email)->send(new \App\Mail\GuestBookingOtp($booking, $guestOtp, $tripUrl));

            return back()
                ->with('success', 'A verification link has been sent to your email. Please check your inbox and use the email link to access your guest trip.');
        } catch (\Exception $e) {
            Log::error('Failed to send guest order verification link', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage = config('app.debug') ? $e->getMessage() : 'Failed to send verification link. Please try again later.';
            return back()->with('error', $errorMessage);
        }
    }
}

