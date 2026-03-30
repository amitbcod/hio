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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            ->with('success', 'Item added to your booking cart!');
    }

    private function buildAccommodationCartItem(Request $request): array
    {
        $accommodationId = (int) $request->input('accommodation_id');
        $roomId          = $request->input('room_id') ? (int) $request->input('room_id') : null;
        $checkIn         = $request->input('check_in');
        $checkOut        = $request->input('check_out');
        $adults          = max(1, (int) $request->input('adults', 2));
        $children        = max(0, (int) $request->input('children', 0));
        $nightlyPrice    = (float) $request->input('nightly_price', 0);
        $totalPrice      = (float) $request->input('total_price', 0);
        $currency        = $request->input('currency', 'USD');
        $roomName        = $request->input('room_name', 'Standard Room');
        $nights          = max(1, (int) $request->input('nights', 1));
        $image           = $request->input('image', '');
        $title           = $request->input('title', '');

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
            'nightly_price'    => $nightlyPrice,
            'total_price'      => $totalPrice,
            'currency'         => $currency,
            'discount_amount'  => $discountAmount,
            'tax_amount'       => $taxAmount,
            'fee_amount'       => $feeAmount,
            'net_amount'       => $netAmount,
            'promotion_id'     => $promotionId,
            'is_non_refundable'=> $isNonRefundable,
        ];
    }

    private function buildActivityCartItem(Request $request): array
    {
        $activityId  = (int) $request->input('activity_id');
        $variantId   = $request->input('variant_id') ? (int) $request->input('variant_id') : null;
        $variantName = $request->input('variant_name', '');
        $checkIn     = $request->input('check_in');   // activity date
        $adults      = max(1, (int) $request->input('adults', 2));
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
    //  CHECKOUT (Guest info form)
    // ═══════════════════════════════════════════════════════════════════════

    public function checkout()
    {
        if (!Auth::guard('traveler')->check()) {
            return redirect()->route('traveler.login')->with('error', 'Please log in as a traveler to continue to checkout.');
        }

        $cart = $this->resolveCart();

        if (empty($cart)) {
            return redirect()->route('frontend.home')->with('error', 'Your cart is empty.');
        }

        $summary = $this->buildCartSummary($cart);

        $totalGuests = 0;
        foreach ($cart as $item) {
            $totalGuests += $item['adults'] + $item['children'];
        }

        $traveler = Auth::guard('traveler')->user();
        $travelerProfile = $traveler?->profile;

        $savedGuests = $traveler ? SavedGuest::where('user_id', $traveler->id)->get() : collect();

        $guestDefaults = [
            'guest_name' => old('guest_name') ?: ($traveler?->full_name ?: ($travelerProfile ? trim($travelerProfile->first_name . ' ' . ($travelerProfile->middle_name ?? '') . ' ' . $travelerProfile->last_name) : null)),
            'guest_email' => old('guest_email') ?: ($traveler?->email ?? null),
            'guest_phone' => old('guest_phone') ?: ($traveler?->mobile_phone ?? null),
        ];

        $countries = $this->countries();

        return view('frontend.checkout', compact('cart', 'summary', 'guestDefaults', 'traveler', 'countries', 'totalGuests', 'savedGuests'));
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
            return redirect()->route('frontend.home')->with('error', 'Your cart is empty.');
        }

        $totalGuests = 0;
        foreach ($cart as $item) {
            $totalGuests += $item['adults'] + $item['children'];
        }

        $request->validate([
            'guests' => 'required|array|min:1',
            'guests.*.relation' => 'required|in:self,spouse,child,friend,colleague,other',
            'guests.*.first_name' => 'required|string|max:100',
            'guests.*.middle_name' => 'nullable|string|max:100',
            'guests.*.last_name' => 'required|string|max:100',
            'guests.*.dob' => 'required|date|before:today',
            'guests.*.gender' => 'nullable|in:male,female,non_binary,other',
            'guests.*.nationality' => 'required|string|max:100',
            'guests.*.passport_number' => 'nullable|string|max:100',
            'guests.*.notes' => 'nullable|string|max:1000',
            'guest_email' => 'nullable|email|max:150',
            'guest_phone' => 'nullable|string|max:30',
        ]);

        $guests = $request->input('guests');
        $guestEmail = $request->input('guest_email');
        $guestPhone = $request->input('guest_phone');
        $special    = $request->input('special_requests', '');

        // Use the first item's currency / grand total
        $summary = $this->buildCartSummary($cart);
        $bookingRefs = [];

        foreach ($cart as $item) {
            $ref = $this->generateBookingRef($item['type']);

            $travelerAccountId = Auth::guard('traveler')->id() ?? null;
            $primaryGuest = $guests[0] ?? null;
            $guestName = trim(($primaryGuest['first_name'] ?? '') . ' ' . ($primaryGuest['middle_name'] ?? '') . ' ' . ($primaryGuest['last_name'] ?? ''));

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
                ]);

                // Store all guests
                foreach ($guests as $index => $guest) {
                    BookingGuest::create([
                        'booking_id' => $booking->id,
                        'booking_type' => 'accommodation',
                        'guest_number' => $index + 1,
                        'relation' => $guest['relation'],
                        'first_name' => $guest['first_name'],
                        'middle_name' => $guest['middle_name'],
                        'last_name' => $guest['last_name'],
                        'dob' => $guest['dob'],
                        'gender' => $guest['gender'],
                        'nationality' => $guest['nationality'],
                        'passport_number' => $guest['passport_number'],
                        'notes' => $guest['notes'],
                    ]);
                }
            } elseif ($item['type'] === 'activity') {
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
                    'activity_date'     => $item['check_in'],
                    'adults'            => $item['adults'],
                    'children'          => $item['children'],
                    'booking_status'    => 'Pending',
                    'total_amount'      => $item['net_amount'],
                    'currency'          => $item['currency'],
                    'payment_method'    => 'COD',
                    'source_channel'    => 'Direct',
                    'special_requests'  => $special,
                    'booked_at'         => now(),
                ]);

                // Store all guests
                foreach ($guests as $index => $guest) {
                    BookingGuest::create([
                        'booking_id' => $booking->id,
                        'booking_type' => 'activity',
                        'guest_number' => $index + 1,
                        'relation' => $guest['relation'],
                        'first_name' => $guest['first_name'],
                        'middle_name' => $guest['middle_name'],
                        'last_name' => $guest['last_name'],
                        'dob' => $guest['dob'],
                        'gender' => $guest['gender'],
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

    private function generateBookingRef(string $type): string
    {
        $prefix = $type === 'accommodation' ? 'ACC' : 'ACT';
        return $prefix . '-' . strtoupper(substr(uniqid(), -8)) . '-' . now()->format('Ymd');
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

    private function travelerAccountId(): ?int
    {
        $traveler = Auth::guard('traveler')->user();

        return $traveler ? (int) $traveler->id : null;
    }

    public function saveGuest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:150',
            'middle_name' => 'nullable|string|max:150',
            'last_name' => 'required|string|max:150',
            'dob' => 'required|date|before:today',
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
}
