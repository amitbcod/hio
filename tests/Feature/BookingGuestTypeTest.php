<?php

namespace Tests\Feature;

use App\Models\BookingGuest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingGuestTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_transport_booking_guests_can_be_saved(): void
    {
        $guest = BookingGuest::query()->create([
            'booking_id' => 1,
            'booking_type' => 'transport',
            'guest_number' => 1,
            'first_name' => 'Test',
            'last_name' => 'Traveller',
            'dob' => '1990-01-01',
            'nationality' => 'Mauritian',
        ]);

        $this->assertSame('transport', $guest->fresh()->booking_type);
    }
}
