<?php

namespace App\Services;

use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\Booking;
use App\Models\BookingRef;
use App\Models\Group;
use App\Models\PaymentTransaction;
use App\Models\TransportBooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminGroupBookingSettlementService
{
    public function findEligibleOrder(string $reference): array
    {
        $bookingRef = BookingRef::where('booking_ref_code', trim($reference))->first();
        if (!$bookingRef) {
            throw (new ModelNotFoundException())->setModel(BookingRef::class);
        }

        $parentBooking = Booking::with(['trip.travellers', 'lineItems'])
            ->where('booking_ref_id', $bookingRef->id)
            ->where('booking_type', 'open-group')
            ->where('is_admin_created', true)
            ->first();

        if (!$parentBooking) {
            throw (new ModelNotFoundException())->setModel(Booking::class);
        }

        $paymentTransaction = $this->getOrCreatePaymentTransaction($bookingRef, $parentBooking);
        $groupLineItem = $parentBooking->lineItems->firstWhere('service_type', 'package');
        $group = $groupLineItem ? Group::find($groupLineItem->service_id) : null;

        $items = collect()
            ->concat(AccommodationBooking::with(['accommodation', 'room'])
                ->where('booking_ref_id', $bookingRef->id)
                ->orderBy('check_in_date')
                ->orderBy('id')
                ->get()
                ->map(fn ($booking) => [
                    'type' => 'Accommodation',
                    'name' => $booking->accommodation?->property_name ?: ($booking->accommodation?->name ?: 'Accommodation'),
                    'reference' => $booking->booking_reference,
                    'date' => $booking->check_in_date?->format('Y-m-d'),
                    'end_date' => $booking->check_out_date?->format('Y-m-d'),
                    'details' => $booking->room?->room_name ?: ($booking->room?->room_type ?: 'Room'),
                    'quantity' => max(1, (int) ($booking->rooms_booked ?? 1)),
                    'amount' => (float) ($booking->total_amount ?? 0),
                    'unit_price' => (float) ($booking->total_amount ?? 0) / max(1, (int) ($booking->rooms_booked ?? 1)),
                ]))
            ->concat(ActivityBooking::with('activity')
                ->where('booking_ref_id', $bookingRef->id)
                ->orderBy('activity_date')
                ->orderBy('id')
                ->get()
                ->map(fn ($booking) => [
                    'type' => 'Activity',
                    'name' => $booking->activity?->activity_name ?: ($booking->activity?->name ?: 'Activity'),
                    'reference' => $booking->booking_reference,
                    'date' => $booking->activity_date?->format('Y-m-d'),
                    'end_date' => $booking->activity_date?->format('Y-m-d'),
                    'details' => $booking->variant_name ?: 'Activity booking',
                    'quantity' => max(1, (int) ($booking->adults ?? 1) + (int) ($booking->children ?? 0)),
                    'amount' => (float) ($booking->total_amount ?? 0),
                    'unit_price' => (float) ($booking->total_amount ?? 0) / max(1, (int) ($booking->adults ?? 1) + (int) ($booking->children ?? 0)),
                ]))
            ->concat(TransportBooking::with('transport')
                ->where('booking_ref_id', $bookingRef->id)
                ->orderBy('pickup_date')
                ->orderBy('id')
                ->get()
                ->map(fn ($booking) => [
                    'type' => 'Transport',
                    'name' => $booking->transport?->vehicle_display_name ?: ($booking->transport?->vehicle_name ?: 'Transport'),
                    'reference' => $booking->booking_reference,
                    'date' => $booking->pickup_date?->format('Y-m-d'),
                    'end_date' => $booking->return_date?->format('Y-m-d'),
                    'details' => trim(($booking->route_from ?: 'N/A') . ' to ' . ($booking->route_to ?: 'N/A')),
                    'quantity' => max(1, (int) ($booking->total_passengers ?? $booking->adults ?? 1)),
                    'amount' => (float) ($booking->total_amount ?? 0),
                    'unit_price' => (float) ($booking->total_amount ?? 0) / max(1, (int) ($booking->total_passengers ?? $booking->adults ?? 1)),
                ]))
            ->sortBy('date')
            ->values();

        $leadTraveller = $parentBooking->trip?->travellers
            ?->first(fn ($traveller) => ($traveller->relationship ?? null) === 'lead');

        $total = (float) ($bookingRef->total_amount ?? $parentBooking->total_amount ?? 0);
        $serviceTotal = (float) $items->sum('amount');

        return [
            'booking_ref' => $bookingRef,
            'booking' => $parentBooking,
            'trip' => $parentBooking->trip,
            'group' => $group,
            'traveller' => $leadTraveller,
            'payment' => $paymentTransaction,
            'items' => $items,
            'total' => $total,
            'order_adjustment' => round($total - $serviceTotal, 2),
            'currency' => 'USD',
        ];
    }

    private function getOrCreatePaymentTransaction(BookingRef $bookingRef, Booking $booking): PaymentTransaction
    {
        return DB::transaction(function () use ($bookingRef, $booking): PaymentTransaction {
            $lockedReference = BookingRef::whereKey($bookingRef->id)->lockForUpdate()->firstOrFail();
            $payment = PaymentTransaction::where('booking_ref_id', $lockedReference->id)->lockForUpdate()->first();

            if (!$payment) {
                $payment = PaymentTransaction::create([
                    'booking_id' => $booking->id,
                    'booking_ref_id' => $lockedReference->id,
                    'amount' => $lockedReference->total_amount ?? $booking->total_amount,
                    'method' => 'bank_transfer',
                    'status' => 'pending',
                    'settlement_status' => 'pending_verification',
                ]);
                $lockedReference->payment_transaction_id = $payment->id;
                $lockedReference->save();
            } elseif (!$payment->settlement_status && $payment->status !== 'paid') {
                $payment->settlement_status = 'pending_verification';
                $payment->save();
            }

            return $payment;
        });
    }
}