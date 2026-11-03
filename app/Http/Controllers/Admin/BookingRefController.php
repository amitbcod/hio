<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingRef;

class BookingRefController extends Controller
{
    public function index()
    {
        $bookingRefs = BookingRef::with(['paymentTransaction', 'paymentTransactions', 'bookings.payments'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        $bookingRefs->getCollection()->each(fn (BookingRef $bookingRef) => $bookingRef->setAttribute(
            'payment_status_display',
            $this->resolvePaymentStatusDisplay($bookingRef)
        ));

        return view('admin.booking_refs.index', compact('bookingRefs'));
    }

    public function show(BookingRef $bookingRef)
    {
        $bookingRef->load(['trip', 'paymentTransaction', 'paymentTransactions', 'bookings.payments']);
        $bookingRef->setAttribute('payment_status_display', $this->resolvePaymentStatusDisplay($bookingRef));

        return view('admin.booking_refs.show', compact('bookingRef'));
    }

    private function resolvePaymentStatusDisplay(BookingRef $bookingRef): string
    {
        $payment = $bookingRef->paymentTransactions
            ->concat($bookingRef->bookings->flatMap(fn ($booking) => $booking->payments))
            ->push($bookingRef->paymentTransaction)
            ->filter()
            ->sortByDesc('id')
            ->first();

        if (!$payment) {
            return 'Incomplete - no transaction';
        }

        if ($payment->status === 'pending' && $payment->settlement_status === 'pending_verification') {
            return 'Pending verification';
        }

        if ($payment->status === 'paid' && $payment->settlement_status === 'verified_settled') {
            return 'Verified settled';
        }

        return ucwords(str_replace('_', ' ', $payment->status));
    }
}
