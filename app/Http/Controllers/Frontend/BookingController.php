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
use App\Models\ActivitySchedulingTimeSlot;
use App\Models\ActivityVariant;
use App\Models\BookingGuest;
use App\Models\SavedGuest;
use App\Models\TravelerCart;
use App\Models\TravelerAccount;
use App\Models\TravelerProfile;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\BookingLineItem;
use App\Models\Traveller;
use App\Models\BliTravellerAllocation;
use App\Models\GuestOtpToken;
use App\Models\PaymentTransaction;
use App\Services\AgaingencyPaymentService;
use App\Services\TripService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════════
    //  ADD TO CART
    // ═══════════════════════════════════════════════════════════════════════

    public function addToCart(Request $request)
    {
        $type = $request->input('type'); // 'accommodation' | 'activity'

        if ($type === 'activity') {
            Validator::make($request->all(), [
                'activity_time_slot_id' => [
                    'required',
                    'integer',
                    Rule::exists('activity_scheduling_timeslots', 'timeslot_id')
                        ->where('activity_id', $request->input('activity_id')),
                ],
            ], [
                'activity_time_slot_id.required' => 'Please select an activity time slot before booking.',
                'activity_time_slot_id.exists' => 'Selected time slot is invalid for this activity.',
            ])->validate();

            $variantId = $request->input('variant_id') ? (int) $request->input('variant_id') : null;
            $adults = max(1, (int) $request->input('adults', 1));
            $children = max(0, (int) $request->input('children', 0));
            $infants = max(0, (int) $request->input('infants', 0));
            $participants = $adults + $children + $infants;

            if ($variantId) {
                $variant = ActivityVariant::find($variantId);
                if ($variant && $variant->max_participants !== null && $variant->max_participants > 0 && $participants > $variant->max_participants) {
                    return back()
                        ->withInput()
                        ->with('error', "Maximum participants allowed for this option is {$variant->max_participants}. Please adjust your booking quantities.");
                }
            }
        }

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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item added to cart.',
                'cart' => array_values($cart),
                'summary' => $this->buildCartSummary($cart),
            ]);
        }

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
        $adults      = max(1, (int) $request->input('adults', 1));
        $children    = max(0, (int) $request->input('children', 0));
        $infants     = max(0, (int) $request->input('infants', 0));
        $participants = $adults + $children + $infants;
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

        $activityTimeSlotId = $request->input('activity_time_slot_id') ? (int) $request->input('activity_time_slot_id') : null;
        $activityTimeSlotDisplay = null;
        if ($activityTimeSlotId && $activity) {
            $timeSlot = ActivitySchedulingTimeSlot::find($activityTimeSlotId);
            if ($timeSlot && $timeSlot->activity_id === $activityId) {
                $activityTimeSlotDisplay = trim(($timeSlot->start_time ?? '') . ' - ' . ($timeSlot->end_time ?? '') . ($timeSlot->duration ? ' (' . $timeSlot->duration . ')' : ''));
            }
        }

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
            'activity_time_slot_id' => $activityTimeSlotId,
            'activity_time_slot_display' => $activityTimeSlotDisplay,
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  VIEW CART (Review Page)
    // ═══════════════════════════════════════════════════════════════════════

    public function viewCart(Request $request)
    {
        $cart = $this->resolveCart();

        if ($request->expectsJson()) {
            $summary = $this->buildCartSummary($cart);

            return response()->json([
                'success' => true,
                'cart' => array_values($cart),
                'summary' => $summary,
            ]);
        }

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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart.',
                'cart' => array_values($cart),
                'summary' => $this->buildCartSummary($cart),
            ]);
        }

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
    //  CREATE GUEST ACCOUNT (from checkout)
    // ═══════════════════════════════════════════════════════════════════════

    public function createGuestAccount(Request $request)
    {
        if (!$request->isJson()) {
            return response()->json(['success' => false, 'error' => 'Invalid request.'], 400);
        }

        $email = $request->input('email');
        $password = $request->input('password');
        $passwordConfirmation = $request->input('password_confirmation');
        $firstName = $request->input('first_name');
        $middleName = $request->input('middle_name');
        $lastName = $request->input('last_name');
        $dob = $request->input('dob');
        $gender = $request->input('gender');
        $nationality = $request->input('nationality');
        $guestPhone = $request->input('guest_phone');

        // Validate inputs
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:150', 'unique:traveler_accounts,email'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&^()_+\-=\[\]{};:\"\\|,.<>\/?]/',
            ],
            'first_name' => ['nullable', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'guest_phone' => ['nullable', 'string', 'max:25', 'unique:traveler_accounts,mobile_phone'],
        ], [
            'email.unique' => 'This email address is already in use.',
            'guest_phone.unique' => 'This mobile number is already associated with another account. Please use a different mobile number.',
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        try {
            // Create traveler account
            $account = \App\Models\TravelerAccount::create([
                'traveler_id' => $this->generateTravelerId(),
                'full_name' => trim(($firstName ? $firstName : explode('@', $email)[0]) . ' ' . ($lastName ?? '')),
                'email' => strtolower(trim($email)),
                'mobile_phone' => $guestPhone,
                'password_hash' => \Illuminate\Support\Facades\Hash::make($password),
                'verification_status' => 'Unverified',
                'terms_accepted_at' => now(),
                'terms_version' => 'TNC-2026.03',
                'privacy_accepted_at' => now(),
                'privacy_version' => 'PRIVACY-2026.03',
            ]);

            // Create traveler profile
            $nameParts = $this->splitName(trim(($firstName ? $firstName : explode('@', $email)[0]) . ' ' . ($lastName ?? '')));
            \App\Models\TravelerProfile::create([
                'traveler_account_id' => $account->id,
                'first_name' => $firstName ?: $nameParts['first_name'],
                'middle_name' => $middleName,
                'last_name' => $lastName ?: $nameParts['last_name'],
                'gender' => $gender,
                'date_of_birth' => $dob ?: null,
                'nationality' => $nationality,
                'country' => $nationality,
                'preferred_language' => 'EN',
            ]);

            // Login the user
            Auth::guard('traveler')->login($account);
            $request->session()->regenerate();

            // Sync cart after authentication
            $this->syncCartAfterAuthentication($account->id, $request);

            return response()->json([
                'success' => true,
                'message' => 'Account created and logged in successfully.',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating guest account: ' . $e->getMessage(), [
                'email' => $email,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to create account. Please try again.',
            ], 500);
        }
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
        $paymentMethod = $request->input('payment_method', 'cod');

        Validator::make(['payment_method' => $paymentMethod], [
            'payment_method' => ['required', Rule::in(['cod', 'againgency'])],
        ])->validate();

        // Determine if this is a guest or authenticated checkout
        $isGuestCheckout = !Auth::guard('traveler')->check();
        $travelerAccount = Auth::guard('traveler')->user();
        
        // Validate email address for all checkout flows
        if (empty($guestEmail)) {
            return back()->with('error', 'Email Address is required.');
        }
        if (!filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'Please enter a valid email address.');
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
        $tripBookingIds = [];

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
                    $tripBookingIds[] = $tripBooking->id;
                    $tripBookingIds[] = $tripBooking->id;

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
                } elseif (!empty($item['activity_time_slot_id'])) {
                    $crewCount = max(1, ($item['adults'] ?? 0) + ($item['children'] ?? 0) + ($item['infants'] ?? 0));
                    for ($guestNumber = 1; $guestNumber <= $crewCount; $guestNumber++) {
                        $itemTimeSlotsMap[$guestNumber] = $item['activity_time_slot_id'];
                    }
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
                    'payment_method'    => $paymentMethod === 'againgency' ? 'Againgency' : 'COD',
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

        $primaryRef = $bookingRefs[0] ?? 'UNKNOWN';
        session(['payment_method' => $paymentMethod]);

        if ($paymentMethod === 'againgency') {
            $transactionRef = 'aga_' . Str::uuid();
            $orderId = $primaryRef;
            $firstBookingId = $tripBookingIds[0] ?? null;

            if (!$firstBookingId) {
                return back()->with('error', 'Unable to initialize payment for your booking. Please try again.');
            }

            $paymentTransaction = PaymentTransaction::create([
                'booking_id' => $firstBookingId,
                'amount' => $summary['net_payable'],
                'method' => 'againgency',
                'status' => 'pending',
                'transaction_ref' => $transactionRef,
            ]);

            $callbackUrl = route('frontend.booking.payment.callback');
            $successUrl = route('frontend.booking.payment.return', ['status' => 'success', 'ref' => $primaryRef, 'transaction_ref' => $transactionRef]);
            $failureUrl = route('frontend.booking.payment.return', ['status' => 'failed', 'ref' => $primaryRef, 'transaction_ref' => $transactionRef]);

            // Extract dates from the first cart item for Ecommpay compliance
            $startDate = null;
            $endDate = null;
            if (!empty($cart)) {
                $firstItem = reset($cart);
                if (is_array($firstItem)) {
                    if (($firstItem['type'] ?? null) === 'accommodation') {
                        $startDate = $firstItem['check_in'] ?? null;
                        $endDate = $firstItem['check_out'] ?? null;
                    } elseif (($firstItem['type'] ?? null) === 'activity') {
                        $startDate = $firstItem['check_in'] ?? $firstItem['activity_date'] ?? null;
                        $endDate = $firstItem['check_out'] ?? $firstItem['activity_date'] ?? null;
                    }
                }
            }

            \Illuminate\Support\Facades\Log::channel('payment_dates')->info('Againgency payment flow dates', [
                'transaction_ref' => $transactionRef,
                'order_id' => $orderId,
                'guest_email' => $guestEmail,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_amount' => $summary['net_payable'],
                'currency' => $summary['currency'],
            ]);

            try {
                $paymentSession = AgaingencyPaymentService::createPaymentSession(
                    $orderId,
                    $transactionRef,
                    $guestEmail,
                    $guestName,
                    $summary['net_payable'],
                    $summary['currency'],
                    $successUrl,
                    $failureUrl,
                    $callbackUrl,
                    $startDate,
                    $endDate
                );
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Payment gateway error: ' . $e->getMessage());
            }

            $paymentUrl = $paymentSession['payment_url'];
            $paymentId = $paymentSession['payment_id'] ?? null;

            if ($paymentId) {
                $paymentTransaction->payment_id = $paymentId;
                $paymentTransaction->save();
            }

            $this->storeCart([]);

            return redirect()->away($paymentUrl);
        }

        $this->storeCart([]);

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
        $paymentMethod = session()->get('payment_method');
        $paymentStatus = 'pending';

        if (!$paymentMethod && $booking) {
            // If $booking is a Booking model it will have payments() relation.
            if ($booking instanceof \App\Models\Booking) {
                $latest = $booking->payments()->latest()->first();
            } else {
                // For AccommodationBooking / ActivityBooking, find trip bookings and check their payment transactions
                $tripBookingIds = [];
                if (!empty($booking->trip_id)) {
                    $tripBookingIds = Booking::where('trip_id', $booking->trip_id)->pluck('id')->toArray();
                }
                $latest = $tripBookingIds ? PaymentTransaction::whereIn('booking_id', $tripBookingIds)->latest()->first() : null;
            }

            $paymentMethod = ($latest && $latest->method === 'againgency') ? 'againgency' : 'cod';
        }

        if ($booking && ($paymentMethod === 'againgency' || !$paymentMethod)) {
            if ($booking instanceof \App\Models\Booking) {
                $latestPayment = $booking->payments()->latest()->first();
            } else {
                $tripBookingIds = [];
                if (!empty($booking->trip_id)) {
                    $tripBookingIds = Booking::where('trip_id', $booking->trip_id)->pluck('id')->toArray();
                }
                $latestPayment = $tripBookingIds ? PaymentTransaction::whereIn('booking_id', $tripBookingIds)->latest()->first() : null;
            }

            if ($latestPayment) {
                $paymentStatus = $latestPayment->status;
            }
        }

        return view('frontend.booking-confirmation', compact('booking', 'type', 'ref', 'bookingRefs', 'guestName', 'summary', 'paymentMethod', 'paymentStatus'));
    }

    public function paymentCallback(Request $request)
    {
        $transactionRef = $request->input('transaction_ref') ?: $request->input('reference');
        if (!$transactionRef) {
            return response()->json(['error' => 'Missing payment reference'], 400);
        }

        $paymentTransaction = PaymentTransaction::where('transaction_ref', $transactionRef)->first();
        if (!$paymentTransaction) {
            return response()->json(['error' => 'Payment transaction not found'], 404);
        }

        $status = AgaingencyPaymentService::resolveCallbackStatus($request->input('status', $request->input('payment_status', 'pending')));
        $paymentTransaction->status = $status;

        $paymentId = AgaingencyPaymentService::parsePaymentId($request->json()->all() ?: $request->all());
        if ($paymentId && empty($paymentTransaction->payment_id)) {
            $paymentTransaction->payment_id = $paymentId;
        }

        $paymentTransaction->save();

        // PaymentTransaction.booking relationship may point to Booking model or be null
        $bookingRecord = Booking::find($paymentTransaction->booking_id);
        if ($bookingRecord && $status === 'paid') {
            Booking::where('trip_id', $bookingRecord->trip_id)->update(['status' => 'paid']);
        }

        return response()->json(['success' => true]);
    }

    public function paymentReturn(Request $request)
    {
        $status = AgaingencyPaymentService::resolveCallbackStatus($request->input('status', $request->input('payment_status', 'pending')));
        $transactionRef = $request->input('transaction_ref') ?: $request->input('reference');
        $primaryRef = $request->input('ref');

        if ($transactionRef) {
            $paymentTransaction = PaymentTransaction::where('transaction_ref', $transactionRef)->first();
            if ($paymentTransaction) {
                $paymentId = AgaingencyPaymentService::parsePaymentId($request->json()->all() ?: $request->all());
                if ($paymentId && empty($paymentTransaction->payment_id)) {
                    $paymentTransaction->payment_id = $paymentId;
                }

                if ($status === 'paid') {
                    $paymentTransaction->status = 'paid';
                }

                $paymentTransaction->save();

                $bookingRecord = Booking::find($paymentTransaction->booking_id);
                if ($bookingRecord && $status === 'paid') {
                    Booking::where('trip_id', $bookingRecord->trip_id)->update(['status' => 'paid']);
                }
            }
        }

        if (!$primaryRef) {
            return redirect()->route('frontend.booking.checkout')->with('error', 'Payment return data incomplete.');
        }

        return redirect()->route('frontend.booking.confirmation', ['ref' => $primaryRef])
            ->with('booking_refs', session('booking_refs', [$primaryRef]))
            ->with('guest_name', session('guest_name'))
            ->with('summary', session('summary'))
            ->with('payment_method', 'againgency');
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

    // ═══════════════════════════════════════════════════════════════════════
    //  HELPER METHODS
    // ═══════════════════════════════════════════════════════════════════════

    private function generateTravelerId(): string
    {
        do {
            $candidate = 'TRV' . now()->format('Ymd') . strtoupper(Str::random(6));
        } while (TravelerAccount::where('traveler_id', $candidate)->exists());

        return $candidate;
    }

    private function splitName(string $fullName): array
    {
        $tokens = collect(preg_split('/\s+/', trim($fullName)) ?: [])->filter()->values();

        if ($tokens->isEmpty()) {
            return [
                'first_name' => null,
                'middle_name' => null,
                'last_name' => null,
            ];
        }

        $firstName = (string) $tokens->first();
        $lastName = $tokens->count() > 1 ? (string) $tokens->last() : null;

        $middleName = null;
        if ($tokens->count() > 2) {
            $middleName = $tokens->slice(1, $tokens->count() - 2)->implode(' ');
        }

        return [
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
        ];
    }

    private function syncCartAfterAuthentication(int $travelerId, Request $request): void
    {
        $sessionCart = $request->session()->get('booking_cart', []);

        $storedCartRecord = TravelerCart::where('traveler_account_id', $travelerId)->first();
        $storedCart = is_array($storedCartRecord?->items) ? $storedCartRecord->items : [];

        $merged = $storedCart;
        foreach ($sessionCart as $cartKey => $item) {
            $merged[$cartKey] = $item;
        }

        if (empty($merged)) {
            $request->session()->forget('booking_cart');
        } else {
            $request->session()->put('booking_cart', $merged);
        }

        TravelerCart::updateOrCreate(
            ['traveler_account_id' => $travelerId],
            ['items' => empty($merged) ? null : $merged]
        );
    }
}

