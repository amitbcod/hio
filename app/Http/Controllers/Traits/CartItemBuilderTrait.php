<?php

namespace App\Http\Controllers\Traits;

use App\Models\Accommodation;
use App\Models\AccommodationFee;
use App\Models\AccommodationPromotion;
use App\Models\Activity;
use App\Models\ActivityPromotion;
use App\Models\ActivitySchedulingTimeSlot;
use App\Models\ActivityVariant;
use App\Models\Transport;
use App\Models\TransportRate;
use Carbon\Carbon;
use Illuminate\Http\Request;

trait CartItemBuilderTrait
{
    protected function buildAccommodationCartItem(Request $request): array
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
        $ratePlanId      = $request->input('rate_plan_id') ? (int) $request->input('rate_plan_id') : null;
        $rateName        = $request->input('rate_name', '');
        $mealPlan        = $request->input('meal_plan', '') ?: null;
        $planInclusions  = $request->input('plan_inclusions');
        if (is_string($planInclusions)) {
            $decodedInclusions = json_decode($planInclusions, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedInclusions)) {
                $planInclusions = $decodedInclusions;
            }
        }

        $accommodation = Accommodation::find($accommodationId);

        $taxAmount      = 0.0;
        $feeAmount      = 0.0;
        $discountAmount = 0.0;
        $promotionId    = null;
        $isNonRefundable = false;

        if ($accommodation) {
            $taxAmount = $this->calcAccommodationTax($accommodation, $totalPrice, $adults, $nights);
            $feeAmount = $this->calcAccommodationFees($accommodation, $roomId, $nights);

            $promo = AccommodationPromotion::where('accommodation_id', $accommodationId)
                ->when($roomId, fn($q) => $q->where(function ($q2) use ($roomId) {
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
            'check_in_display' => $checkIn ? Carbon::parse($checkIn)->format('d M Y') : '',
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
            'rate_plan_id'     => $ratePlanId,
            'rate_name'        => $rateName,
            'meal_plan'        => $mealPlan,
            'plan_inclusions'  => is_array($planInclusions) ? $planInclusions : ($planInclusions !== null ? [$planInclusions] : []),
        ];
    }

    protected function buildActivityCartItem(Request $request): array
    {
        $activityId  = (int) $request->input('activity_id');
        $variantId   = $request->input('variant_id') ? (int) $request->input('variant_id') : null;
        $variantName = $request->input('variant_name', '');
        $checkIn     = $request->input('check_in') ?: $request->input('activity_date');
        $adults      = max(1, (int) $request->input('adults', 1));
        $children    = max(0, (int) $request->input('children', 0));
        $infants     = max(0, (int) $request->input('infants', 0));
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
                ->where(function ($q) {
                    $q->whereNull('promo_valid_from')
                      ->orWhere('promo_valid_from', '<=', now()->toDateString());
                })
                ->where(function ($q) {
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

        $timeslotDiscount = (float) $request->input('timeslot_discount_value', 0);
        if ($timeslotDiscount > 0) {
            $discountAmount = $timeslotDiscount;
            $promotionId = null;
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

        $variantName = $variantName ?: optional(ActivityVariant::find($variantId))->variant_name;

        return [
            'cart_key'         => uniqid('actv_', true),
            'type'             => 'activity',
            'activity_id'      => $activityId,
            'variant_id'       => $variantId,
            'variant_name'     => $variantName,
            'title'            => $title ?: ($activity->activity_name ?? 'Activity'),
            'image'            => $image,
            'check_in'         => $checkIn,
            'check_out'        => $checkIn,
            'check_in_display' => $checkIn ? Carbon::parse($checkIn)->format('d M Y') : '',
            'check_out_display'=> $checkIn ? Carbon::parse($checkIn)->format('d M Y') : '',
            'nights'           => 1,
            'adults'           => $adults,
            'children'         => $children,
            'infants'          => $infants,
            'participants'     => $adults + $children + $infants,
            'nightly_price'    => $totalPrice,
            'total_price'      => $totalPrice,
            'currency'         => $currency,
            'discount_amount'  => $discountAmount,
            'timeslot_discount_value' => $timeslotDiscount,
            'tax_amount'       => $taxAmount,
            'fee_amount'       => 0.0,
            'net_amount'       => $netAmount,
            'promotion_id'     => $promotionId,
            'is_non_refundable'=> $isNonRefundable,
            'activity_time_slot_id' => $activityTimeSlotId,
            'activity_time_slot_display' => $activityTimeSlotDisplay,
        ];
    }

    protected function calcAccommodationTax(Accommodation $accommodation, float $totalPrice, int $adults, int $nights): float
    {
        $taxType       = $accommodation->tax_type ?? 'None';
        $chargesType   = $accommodation->tax_charges_type ?? 'Per Unit';
        $valueType     = $accommodation->tax_charges_value_type ?? 'Percentage';
        $value         = (float) ($accommodation->tax_charges_value ?? 0);

        if ($taxType === 'None' || $value <= 0) {
            return 0.0;
        }

        $base = match ($chargesType) {
            'Per Person', 'Per Adult' => $totalPrice,
            default => $totalPrice,
        };

        if ($valueType === 'Percentage') {
            return round($base * $value / 100, 2);
        }

        $multiplier = match ($chargesType) {
            'Per Person', 'Per Adult' => ($adults * $nights),
            default => $nights,
        };

        return round($value * $multiplier, 2);
    }

    protected function calcAccommodationFees(Accommodation $accommodation, ?int $roomId, int $nights): float
    {
        try {
            $fee = AccommodationFee::where('accommodation_id', $accommodation->id)
                ->when($roomId, fn($q) => $q->where(function ($q2) use ($roomId) {
                    $q2->whereNull('room_id')->orWhere('room_id', $roomId);
                }))
                ->first();
        } catch (\Illuminate\Database\QueryException $e) {
            return 0.0;
        }

        if (!$fee) {
            return 0.0;
        }

        $total = 0.0;
        $total += (float) ($fee->cleaning_fee ?? 0);
        $total += (float) ($fee->resort_fee ?? 0) * $nights;

        return round($total, 2);
    }

    protected function calcActivityTax(Activity $activity, float $totalPrice, int $adults): float
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

        $basis = $accounting->tax_charges_basis ?? 'Per Person';
        $multiplier = str_contains($basis, 'Person') ? $adults : 1;

        return round($value * $multiplier, 2);
    }

    protected function calcPromoDiscount(string $discountType, float $discountValue, float $totalPrice, int $nights): float
    {
        if ($discountType === 'Percentage') {
            return round($totalPrice * $discountValue / 100, 2);
        }

        if (str_contains($discountType, 'Night')) {
            return round($discountValue * $nights, 2);
        }

        return round(min($discountValue, $totalPrice), 2);
    }

    protected function buildTransportCartItem(Request $request): array
    {
        $transportId = (int) $request->input('transport_id');
        $rateId = $request->input('rate_id') ? (int) $request->input('rate_id') : null;
        $routeId = $request->input('route_id', null);
        $routeFrom = $request->input('route_from', '');
        $routeTo = $request->input('route_to', '');
        $pickupDate = $request->input('pickup_date');
        $pickupTime = $request->input('pickup_time', '');
        $returnDate = $request->input('return_date');
        $returnTime = $request->input('return_time', '');
        $passengers = max(1, (int) $request->input('passengers', 1));
        $pricePerPassenger = (float) $request->input('price_per_passenger', 0);
        $returnPrice = max(0.0, (float) $request->input('return_price', 0));
        $carRentalTotal = (float) $request->input('car_rental_total', 0);
        $serviceType = $request->input('service_type', 'route');
        $currency = $request->input('currency', 'USD');
        $image = $request->input('image', '');
        $title = $request->input('title', '');

        $transport = Transport::find($transportId);
        if (!$transport) {
            return [];
        }

        // Calculate total price - use car rental total for car_rental service type
        if ($serviceType === 'car_rental' && $carRentalTotal > 0) {
            $totalPrice = $carRentalTotal;
            $pricePerPassenger = 0; // Not used for car rental
        } else {
            $totalPrice = $pricePerPassenger * $passengers;
            if (!blank($returnDate) && $returnPrice > 0) {
                $totalPrice += $returnPrice * $passengers;
            }
        }
        
        $taxAmount = 0.0;
        $discountAmount = 0.0;

        // If transport has tax configuration, calculate tax
        if ($transport->tax_type && $transport->tax_value) {
            $taxAmount = $this->calcTransportTax($transport, $totalPrice);
        }

        $priceAfterDiscount = max(0, $totalPrice - $discountAmount);
        $netAmount = $priceAfterDiscount + $taxAmount;

        return [
            'cart_key' => uniqid('transport_', true),
            'type' => 'transport',
            'service_type' => $serviceType,
            'transport_id' => $transportId,
            'rate_id' => $rateId,
            'route_id' => $routeId,
            'route_from' => $routeFrom,
            'route_to' => $routeTo,
            'title' => $title ?: ($transport->vehicle_name ?? 'Transport'),
            'image' => $image,
            'pickup_date' => $pickupDate,
            'pickup_date_display' => $pickupDate ? Carbon::parse($pickupDate)->format('d M Y') : '',
            'pickup_time' => $pickupTime,
            'return_date' => $returnDate,
            'return_date_display' => $returnDate ? Carbon::parse($returnDate)->format('d M Y') : '',
            'return_time' => $returnTime,
            'passengers' => $passengers,
            'price_per_passenger' => $pricePerPassenger,
            'return_price' => $returnPrice,
            'car_rental_total' => $carRentalTotal,
            'total_price' => $totalPrice,
            'currency' => $currency,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'net_amount' => $netAmount,
            'vehicle_type' => $transport->vehicle_type,
            'seating_capacity' => $transport->seating_capacity,
        ];
    }

    protected function calcTransportTax(Transport $transport, float $baseAmount): float
    {
        if (!$transport->tax_type || !$transport->tax_value) {
            return 0.0;
        }

        if ($transport->tax_type === 'Percentage') {
            return round($baseAmount * $transport->tax_value / 100, 2);
        }

        // Fixed amount
        return round($transport->tax_value, 2);
    }
}
