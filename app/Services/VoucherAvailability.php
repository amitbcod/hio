<?php

namespace App\Services;

use App\Models\ActivityBooking;
use App\Models\Booking;
use App\Models\BookingLineItem;
use App\Models\PaymentTransaction;
use App\Models\Trip;
use App\Models\Traveller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class VoucherAvailability
{
    public function isAvailable(Trip $trip, Model $serviceBooking, string $serviceType): bool
    {
        if (! $this->hasSuccessfulPayment($trip, $serviceBooking, $serviceType)) {
            return false;
        }

        if ($serviceType !== 'activity') {
            return true;
        }

        return $serviceBooking instanceof ActivityBooking
            && $this->hasCompleteActivityParticipants($trip, $serviceBooking);
    }

    private function hasSuccessfulPayment(Trip $trip, Model $serviceBooking, string $serviceType): bool
    {
        $bookingRefId = $serviceBooking->getAttribute('booking_ref_id');
        if ($bookingRefId !== null) {
            $bookingRef = $trip->relationLoaded('bookingRefs')
                ? $trip->bookingRefs->firstWhere('id', (int) $bookingRefId)
                : $trip->bookingRefs()->with(['paymentTransactions', 'paymentTransaction'])->find($bookingRefId);

            if (
                $bookingRef
                && (int) $bookingRef->getKey() === (int) $bookingRefId
                && (int) $bookingRef->trip_id === (int) $trip->id
            ) {
                if ($this->hasPaidTransaction($bookingRef->paymentTransactions ?? collect())) {
                    return true;
                }

                $singlePayment = $bookingRef->paymentTransaction;
                if ($singlePayment && $this->isPaid($singlePayment)) {
                    return true;
                }
            }

            return $this->hasPaidParentBooking($trip, (int) $bookingRefId);
        }

        $matchingBookings = $this->matchingParentBookings($trip, $serviceType, $serviceBooking);
        if ($matchingBookings->count() !== 1) {
            return false;
        }

        foreach ($matchingBookings as $parentBooking) {
            if ($this->hasPaidTransaction($parentBooking->payments ?? collect())) {
                return true;
            }
        }

        return false;
    }

    private function hasPaidParentBooking(
        Trip $trip,
        int $bookingRefId
    ): bool {
        return $trip->bookings
            ->filter(fn (Booking $booking) => (int) $booking->booking_ref_id === $bookingRefId)
            ->contains(fn (Booking $booking) => $this->hasPaidTransaction($booking->payments ?? collect()));
    }

    private function matchingParentBookings(Trip $trip, string $serviceType, Model $serviceBooking): Collection
    {
        $serviceIdAttribute = match ($serviceType) {
            'accommodation' => 'accommodation_id',
            'activity' => 'activity_id',
            'transport' => 'transport_id',
            default => null,
        };
        $serviceId = $serviceIdAttribute ? $serviceBooking->getAttribute($serviceIdAttribute) : null;

        return $trip->bookings->filter(function (Booking $booking) use ($serviceType, $serviceBooking, $serviceId) {
            return $booking->lineItems->contains(function (BookingLineItem $lineItem) use ($serviceType, $serviceBooking, $serviceId) {
                if ($serviceType === 'transport' && (int) $lineItem->transport_booking_id === (int) $serviceBooking->getKey()) {
                    return true;
                }

                return $serviceId !== null
                    && strtolower((string) $lineItem->service_type) === $serviceType
                    && (int) $lineItem->service_id === (int) $serviceId;
            });
        });
    }

    private function hasCompleteActivityParticipants(Trip $trip, ActivityBooking $booking): bool
    {
        $requiredCount = max(
            0,
            (int) ($booking->adults ?? 0)
                + (int) ($booking->children ?? 0)
                + (int) ($booking->infants ?? 0)
        );

        if ($requiredCount === 0) {
            return false;
        }

        $participants = $booking->relationLoaded('guests')
            ? $booking->getRelation('guests')
            : $booking->guests()->get();

        if ($participants->isEmpty()) {
            return $this->hasCompletePackageParticipants($trip, $booking, $requiredCount);
        }

        if ($participants->count() !== $requiredCount) {
            return false;
        }

        $guestNumbers = [];
        foreach ($participants as $participant) {
            foreach (['first_name', 'last_name', 'dob', 'nationality'] as $requiredField) {
                if (trim((string) $participant->getAttribute($requiredField)) === '') {
                    return false;
                }
            }

            $guestNumber = $participant->getAttribute('guest_number');
            if ($guestNumber === null || (int) $guestNumber < 1) {
                return false;
            }
            $guestNumbers[] = (int) $guestNumber;
        }

        return count(array_unique($guestNumbers)) === count($guestNumbers);
    }

    private function hasCompletePackageParticipants(Trip $trip, ActivityBooking $booking, int $requiredCount): bool
    {
        $bookingRefId = $booking->getAttribute('booking_ref_id');
        if ($bookingRefId === null) {
            return false;
        }

        $parentBookings = $trip->relationLoaded('bookings')
            ? $trip->bookings
            : $trip->bookings()->with('lineItems.travellers')->where('booking_ref_id', $bookingRefId)->get();

        $packageLineItems = $parentBookings
            ->filter(fn (Booking $parent) => (int) $parent->booking_ref_id === (int) $bookingRefId)
            ->flatMap(fn (Booking $parent) => $parent->lineItems)
            ->filter(fn (BookingLineItem $lineItem) => strtolower((string) $lineItem->service_type) === 'package')
            ->values();

        if ($packageLineItems->count() !== 1) {
            return false;
        }

        $travellers = $packageLineItems->first()->relationLoaded('travellers')
            ? $packageLineItems->first()->getRelation('travellers')
            : $packageLineItems->first()->travellers()->where('trip_id', $trip->id)->get();
        $travellers = $travellers->where('trip_id', $trip->id)->values();

        if ($travellers->count() !== $requiredCount) {
            return false;
        }

        foreach ($travellers as $traveller) {
            if (! $traveller instanceof Traveller) {
                return false;
            }

            $nameParts = preg_split('/\s+/', trim((string) $traveller->name), -1, PREG_SPLIT_NO_EMPTY);
            if (count($nameParts) < 2 || empty($traveller->date_of_birth)) {
                return false;
            }
        }

        return true;
    }

    private function hasPaidTransaction(iterable $transactions): bool
    {
        foreach ($transactions as $transaction) {
            if ($transaction instanceof PaymentTransaction && $this->isPaid($transaction)) {
                return true;
            }
        }

        return false;
    }

    private function isPaid(PaymentTransaction $transaction): bool
    {
        $status = strtolower(trim((string) $transaction->status));
        $settlementStatus = strtolower(trim((string) $transaction->settlement_status));
        $unsuccessfulStatuses = ['failed', 'refunded', 'rejected', 'cancel', 'cancelled', 'canceled'];
        if (
            in_array($status, $unsuccessfulStatuses, true)
            || in_array($settlementStatus, $unsuccessfulStatuses, true)
        ) {
            return false;
        }

        return $settlementStatus !== ''
            ? in_array($settlementStatus, ['paid', 'verified_settled'], true)
            : $status === 'paid';
    }
}
