<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\Trip;
use App\Models\BookingRef;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TripListingFilters
{
    private ?bool $hasSettlementStatus = null;

    public function apply(Builder $query, array $filters, ?array $operatorServiceIds = null): Builder
    {
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        if ($fromDate && $toDate) {
            $query->whereRaw('COALESCE(trips.start_date, trips.end_date) <= ?', [$toDate])
                ->whereRaw('COALESCE(trips.end_date, trips.start_date) >= ?', [$fromDate]);
        } elseif ($fromDate) {
            $query->whereRaw('COALESCE(trips.start_date, trips.end_date) >= ?', [$fromDate]);
        } elseif ($toDate) {
            $query->whereRaw('COALESCE(trips.start_date, trips.end_date) <= ?', [$toDate]);
        }

        if (!empty($filters['payment_status'])) {
            $this->applyPaymentStatus($query, $filters['payment_status']);
        }

        if (!empty($filters['trip_type'])) {
            $this->applyTripType($query, $filters['trip_type']);
        }

        if (!empty($filters['traveller'])) {
            $term = '%' . mb_strtolower(trim($filters['traveller'])) . '%';
            $query->whereHas('traveler', fn (Builder $travelerQuery) => $travelerQuery
                ->whereRaw('LOWER(traveler_accounts.full_name) LIKE ?', [$term]));
        }

        if (!empty($filters['booking_reference'])) {
            $term = '%' . mb_strtolower(trim($filters['booking_reference'])) . '%';
            $this->applyBookingReference($query, $term, $operatorServiceIds);
        }

        if (!empty($filters['trip'])) {
            $term = trim($filters['trip']);
            $query->where(function (Builder $tripQuery) use ($term) {
                $tripQuery->whereRaw('LOWER(trips.title) LIKE ?', ['%' . mb_strtolower($term) . '%']);

                if (ctype_digit($term)) {
                    $tripQuery->orWhere('trips.id', (int) $term);
                }
            });
        }

        return $query;
    }

    public function paymentStatusOptions(Builder $tripScope): array
    {
        $tripIds = (clone $tripScope)->reorder()->select('trips.id');
        $transactions = PaymentTransaction::query()->where(function (Builder $query) use ($tripIds) {
            $query->whereHas('booking', fn (Builder $bookingQuery) => $bookingQuery->whereIn('trip_id', $tripIds))
                ->orWhereHas('bookingRef', fn (Builder $referenceQuery) => $referenceQuery->whereIn('trip_id', $tripIds))
                ->orWhereIn('payment_transactions.id', BookingRef::query()
                    ->whereIn('trip_id', $tripIds)
                    ->select('payment_transaction_id'));
        });

        $statuses = (clone $transactions)
            ->whereNotNull('status')
            ->distinct()
            ->pluck('status');

        if ($this->hasSettlementStatusColumn()) {
            $statuses = $statuses->merge(
                (clone $transactions)
                    ->whereNotNull('settlement_status')
                    ->distinct()
                    ->pluck('settlement_status')
            );
        }

        return $statuses
            ->filter(fn ($status) => is_string($status) && trim($status) !== '')
            ->unique(fn ($status) => mb_strtolower($status))
            ->sortBy(fn ($status) => mb_strtolower($this->formatPaymentStatus($status)))
            ->mapWithKeys(fn ($status) => [mb_strtolower($status) => $this->formatPaymentStatus($status)])
            ->all();
    }

    public function resolvePaymentStatusDisplay(Trip $trip): string
    {
        $payments = collect();

        foreach ($trip->bookings as $booking) {
            $payments = $payments->merge($booking->payments ?? collect());
        }

        foreach ($trip->bookingRefs as $bookingRef) {
            $payments = $payments->merge($bookingRef->paymentTransactions ?? collect());
            if ($bookingRef->paymentTransaction) {
                $payments->push($bookingRef->paymentTransaction);
            }
        }

        $statuses = $payments
            ->unique(fn (PaymentTransaction $payment) => $payment->getKey() ?? spl_object_id($payment))
            ->map(function (PaymentTransaction $payment) {
                $status = $this->hasSettlementStatusColumn() ? $payment->settlement_status : null;

                return $this->formatPaymentStatus($status ?: (string) $payment->status);
            })
            ->filter()
            ->unique()
            ->values();

        return $statuses->isEmpty()
            ? $this->formatPaymentStatus('pending')
            : $statuses->implode(', ');
    }

    public function formatPaymentStatus(string $status): string
    {
        $normalized = mb_strtolower(trim($status));

        if ($normalized === 'verified_settled') {
            return 'Verified & Settled';
        }

        return Str::headline(str_replace('_', ' ', $normalized));
    }

    public function resolveTripType(Trip $trip): string
    {
        $bookings = $trip->relationLoaded('bookings')
            ? $trip->bookings
            : $trip->bookings()->with('lineItems')->get();

        foreach ($bookings as $booking) {
            $bookingType = mb_strtolower((string) ($booking->booking_type ?? ''));
            if (in_array($bookingType, ['open-group', 'close-group', 'group'], true)) {
                return 'Group Trip';
            }

            foreach ($booking->lineItems ?? collect() as $lineItem) {
                if (mb_strtolower((string) ($lineItem->service_type ?? '')) === 'package') {
                    return 'Package Trip';
                }
            }

            if ($bookingType === 'package') {
                return 'Package Trip';
            }
        }

        return 'Trip';
    }

    private function applyPaymentStatus(Builder $query, string $status): void
    {
        $status = mb_strtolower(trim($status));
        $hasMatchingPayment = function (Builder $paymentQuery) use ($status): void {
            $paymentQuery->whereRaw('LOWER(payment_transactions.status) = ?', [$status]);

            if ($this->hasSettlementStatusColumn()) {
                $paymentQuery->orWhereRaw('LOWER(payment_transactions.settlement_status) = ?', [$status]);
            }
        };

        $query->where(function (Builder $tripQuery) use ($hasMatchingPayment, $status) {
            $tripQuery->whereHas('bookings.payments', $hasMatchingPayment)
                ->orWhereHas('bookingRefs.paymentTransactions', $hasMatchingPayment)
                ->orWhereHas('bookingRefs.paymentTransaction', $hasMatchingPayment);

            if ($status === 'pending') {
                $tripQuery->orWhere(function (Builder $unpaidTripQuery) {
                    $unpaidTripQuery->whereDoesntHave('bookings.payments')
                        ->whereDoesntHave('bookingRefs.paymentTransactions')
                        ->whereDoesntHave('bookingRefs.paymentTransaction');
                });
            }
        });
    }

    private function applyTripType(Builder $query, string $tripType): void
    {
        $groupTypes = ['open-group', 'close-group', 'group'];
        $groupPlaceholders = implode(', ', array_fill(0, count($groupTypes), '?'));
        $hasGroupBooking = fn (Builder $bookingQuery) => $bookingQuery
            ->whereRaw("LOWER(booking_type) IN ({$groupPlaceholders})", $groupTypes);
        $hasPackageBooking = fn (Builder $bookingQuery) => $bookingQuery
            ->whereRaw('LOWER(booking_type) = ?', ['package']);
        $hasPackageLineItem = fn (Builder $lineItemQuery) => $lineItemQuery->whereRaw('LOWER(service_type) = ?', ['package']);

        if ($tripType === 'Group Trip') {
            $query->whereHas('bookings', $hasGroupBooking);
        } elseif ($tripType === 'Package Trip') {
            $query->where(function (Builder $tripQuery) use ($hasPackageBooking, $hasPackageLineItem) {
                $tripQuery->whereHas('bookings', $hasPackageBooking)
                    ->orWhereHas('bookings.lineItems', $hasPackageLineItem);
            })->whereDoesntHave('bookings', $hasGroupBooking);
        } elseif ($tripType === 'Trip') {
            $query->whereDoesntHave('bookings', $hasGroupBooking)
                ->whereDoesntHave('bookings', $hasPackageBooking)
                ->whereDoesntHave('bookings.lineItems', $hasPackageLineItem);
        }
    }

    private function applyBookingReference(Builder $query, string $term, ?array $operatorServiceIds): void
    {
        $query->where(function (Builder $tripQuery) use ($term, $operatorServiceIds) {
            if ($operatorServiceIds === null) {
                $tripQuery->whereHas('bookingRefs', fn (Builder $referenceQuery) => $referenceQuery
                    ->whereRaw('LOWER(booking_ref_code) LIKE ?', [$term]));
            }

            $relations = [
                'accommodationBookings' => $operatorServiceIds['accommodations'] ?? null,
                'activityBookings' => $operatorServiceIds['activities'] ?? null,
                'transportBookings' => $operatorServiceIds['transports'] ?? null,
            ];

            $hasSearchableService = false;
            foreach ($relations as $relation => $serviceIds) {
                if ($operatorServiceIds !== null && $serviceIds?->isEmpty()) {
                    continue;
                }

                $hasSearchableService = true;
                $tripQuery->orWhereHas($relation, function (Builder $bookingQuery) use ($relation, $serviceIds, $term) {
                    if ($serviceIds !== null) {
                        $serviceColumn = match ($relation) {
                            'accommodationBookings' => 'accommodation_id',
                            'activityBookings' => 'activity_id',
                            default => 'transport_id',
                        };
                        $bookingQuery->whereIn($serviceColumn, $serviceIds);
                    }

                    $bookingQuery->where(function (Builder $referenceQuery) use ($term) {
                        $referenceQuery->whereRaw('LOWER(booking_reference) LIKE ?', [$term])
                            ->orWhereHas('bookingRef', fn (Builder $bookingRefQuery) => $bookingRefQuery
                                ->whereRaw('LOWER(booking_ref_code) LIKE ?', [$term]));
                    });
                });
            }

            if ($operatorServiceIds !== null && !$hasSearchableService) {
                $tripQuery->whereRaw('1 = 0');
            }
        });
    }

    private function hasSettlementStatusColumn(): bool
    {
        return $this->hasSettlementStatus ??= Schema::hasColumn('payment_transactions', 'settlement_status');
    }
}
