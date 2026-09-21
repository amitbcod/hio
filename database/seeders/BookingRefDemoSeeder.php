<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trip;
use App\Models\AccommodationBooking;
use App\Models\TransportBooking;
use App\Models\PaymentTransaction;
use App\Models\BookingRef;

class BookingRefDemoSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding BookingRef demo...\n";

        $trip = Trip::create([
            'traveler_account_id' => null,
            'title' => 'Demo Trip',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'status' => 'planned',
        ]);

        $accRef = 'ACC-' . $trip->id . '-' . now()->format('Ymd') . '-1';
        $trsRef = 'TRS-' . $trip->id . '-' . now()->format('Ymd') . '-1';

        $acc = AccommodationBooking::create([
            'booking_reference' => $accRef,
            'accommodation_id' => 1,
            'guest_name' => 'Demo Guest',
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'rooms_booked' => 1,
            'adults' => 2,
            'booking_status' => 'Pending',
            'total_amount' => 100.00,
            'currency' => 'USD',
            'source_channel' => 'Direct',
            'booked_at' => now(),
            'trip_id' => $trip->id,
        ]);

        $trs = TransportBooking::create([
            'booking_reference' => $trsRef,
            'transport_id' => 1,
            'guest_name' => 'Demo Guest',
            'guest_email' => 'demo@example.com',
            'guest_phone' => null,
            'route_from' => 'A',
            'route_to' => 'B',
            'pickup_date' => now()->toDateString(),
            'adults' => 2,
            'total_amount' => 50.00,
            'currency' => 'USD',
            'booking_status' => 'Pending',
            'source_channel' => 'Direct',
            'booked_at' => now(),
            'trip_id' => $trip->id,
        ]);

        echo "Created service bookings: {$acc->booking_reference}, {$trs->booking_reference}\n";

        // Create a generic Booking record to associate the payment transaction
        $genericBooking = \App\Models\Booking::create([
            'trip_id' => $trip->id,
            'operator_id' => null,
            'total_amount' => 150.00,
            'status' => 'pending',
            'booking_type' => 'direct',
        ]);

        $payment = PaymentTransaction::create([
            'booking_id' => $genericBooking->id,
            'amount' => 150.00,
            'method' => 'againgency',
            'status' => 'pending',
            'transaction_ref' => 'aga_' . \Illuminate\Support\Str::uuid(),
        ]);

        // Create BookingRef and link
        $bookingRef = BookingRef::create([
            'trip_id' => $trip->id,
            'booking_ref_code' => 'BR-' . $trip->id . '-' . now()->format('Ymd') . '-1',
            'total_amount' => 150.00,
            'payment_transaction_id' => null,
        ]);

        AccommodationBooking::where('booking_reference', $accRef)->update(['booking_ref_id' => $bookingRef->id]);
        TransportBooking::where('booking_reference', $trsRef)->update(['booking_ref_id' => $bookingRef->id]);

        $payment->booking_ref_id = $bookingRef->id;
        $payment->save();

        $bookingRef->payment_transaction_id = $payment->id;
        $bookingRef->save();

        // Reload updated models to display linked booking_ref_id
        $acc = AccommodationBooking::find($acc->id);
        $trs = TransportBooking::find($trs->id);
        $payment = PaymentTransaction::find($payment->id);

        echo "Created BookingRef: {$bookingRef->booking_ref_code} (ID: {$bookingRef->id})\n";
        echo "Linked Accommodation ID: {$acc->id} -> booking_ref_id={$acc->booking_ref_id}\n";
        echo "Linked Transport ID: {$trs->id} -> booking_ref_id={$trs->booking_ref_id}\n";
        echo "Payment transaction ID: {$payment->id} linked to booking_ref_id={$payment->booking_ref_id}\n";
    }
}
