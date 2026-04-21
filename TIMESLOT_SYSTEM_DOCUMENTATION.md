# Activity Booking Timeslot System - Documentation

## Overview
The holidays.io system manages participant timeslots for activity bookings. This document explains how the system works, addresses current issues, and outlines the architecture.

## Current System Architecture

### Data Storage
- **Table**: `activity_bookings`
- **Column**: `participant_time_slots` (JSON)
- **Structure**: `{ "1": "timeslot_123", "2": "timeslot_456" }`
  - Key: `guest_number` (1-indexed)
  - Value: `timeslot_id` from `activity_scheduling_timeslots` table

### Related Tables
- **activity_bookings**: Main booking record (one per activity per traveler)
- **booking_guests**: Individual guest/participant details
- **activity_scheduling_timeslots**: Available time slots for an activity
  - Primary Key: `timeslot_id`
  - Properties: `start_time`, `end_time`, `duration`, etc.

## User Journey

### 1. During Checkout (`/checkout`)
- User selects activity/activities and adds guests
- For each activity, they select timeslot for each participant
- Data structure: `participant_time_slots_json` (nested by cart_key)
- Stored in ActivityBooking upon creation

### 2. During Manage Guests (`/traveler/trips/{trip}/booking/{booking}/manage-guests`)
- User can view, add, edit, delete guests
- Each guest can have a timeslot assigned
- Adding saved guests from guest list includes timeslot selection
- All changes saved to `participant_time_slots` in ActivityBooking

### 3. Downloading Voucher
- System validates all participants have timeslots assigned
- Timeslot info displayed on voucher PDF
- Uses `participant_time_slots` to lookup timeslot details

## Issues Fixed (2026-04-21)

### Issue 1: Timeslot Display Bug After Reload
**Problem**: After adding a guest with timeslot and reloading the page, the timeslot was not displaying.

**Root Cause**: PHP code was querying using wrong primary key
```php
// WRONG (was using 'id')
$timeSlot = $activityTimeSlots->where('id', $timeSlotId)->first();

// CORRECT (using 'timeslot_id')
$timeSlot = $activityTimeSlots->where('timeslot_id', $timeSlotId)->first();
```

**Files Fixed**:
- [resources/views/frontend/traveler/manage-guests.blade.php](resources/views/frontend/traveler/manage-guests.blade.php#L245)
- [app/Http/Controllers/Frontend/TripController.php](app/Http/Controllers/Frontend/TripController.php#L309)

**Status**: ✅ FIXED

## Multiple Activities & Per-Activity Timeslots

### Current State
**Yes, this functionality ALREADY EXISTS** ✅

**How it works**:
- Each activity booking is a separate record in `activity_bookings`
- Each booking has its own `participant_time_slots` JSON column
- If a user books 2 different activities:
  - 2 separate ActivityBooking records created
  - Each booking stores its participants' timeslots independently
  - Each participant can have different timeslots for each activity

### Example
```
User: John Doe
Activity 1: Snorkeling on 2026-06-15
  - Booking ID: 1
  - Participant "John Doe" → Timeslot: 09:00-10:00
  - Participant "Jane Smith" → Timeslot: 10:00-11:00

Activity 2: Hiking on 2026-06-16
  - Booking ID: 2
  - Participant "John Doe" → Timeslot: 14:00-16:00 (different from Activity 1)
  - Participant "Jane Smith" → Timeslot: 14:00-16:00 (different from Activity 1)
```

Each participant can have DIFFERENT timeslots for each activity.

## Data Flow

### Adding Guests with Timeslots (Manage Guests Page)

```javascript
// JavaScript gathers data
guestData = {
  0: {
    first_name: "John",
    last_name: "Doe",
    dob: "1990-01-01",
    time_slot: "timeslot_123",  // ← Timeslot ID
    ...
  },
  1: {
    first_name: "Jane",
    last_name: "Smith",
    dob: "1992-05-15",
    time_slot: "timeslot_124",  // ← Different timeslot
    ...
  }
}

// Form submission creates hidden inputs
// guests[0][time_slot]=timeslot_123
// guests[1][time_slot]=timeslot_124
```

### Backend Processing (TripController::updateGuests)

```php
// Input validation requires time_slot for activity bookings
'guests.*.time_slot' => ($booking instanceof ActivityBooking ? 'required|string' : 'nullable|string')

// Create guests
foreach (array_values($guestInput) as $index => $guestData) {
    $guestData['guest_number'] = $index + 1;  // 1-indexed
    $booking->guests()->create($guestData);
}

// Save participant_time_slots
if ($booking instanceof ActivityBooking) {
    $participantTimeSlots = [];
    foreach (array_values($guestInput) as $index => $guestData) {
        $participantTimeSlots[$index + 1] = $guestData['time_slot'] ?? '';
    }
    $booking->update(['participant_time_slots' => $participantTimeSlots]);
}
```

## Key Components

### ActivityBooking Model
```php
class ActivityBooking extends Model {
    protected $casts = [
        'participant_time_slots' => 'array',  // ← Auto JSON encode/decode
    ];
    
    public function guests() {
        return $this->hasMany(BookingGuest::class, 'booking_id')
                    ->where('booking_type', 'activity');
    }
}
```

### ActivitySchedulingTimeSlot Model
```php
class ActivitySchedulingTimeSlot extends Model {
    protected $primaryKey = 'timeslot_id';  // ← IMPORTANT: Not 'id'
    
    // Properties
    protected $fillable = ['start_time', 'end_time', 'duration', ...];
}
```

## Testing Checklist

- [ ] Add activity booking with 1 participant and timeslot
- [ ] Reload manage-guests page - timeslot displays correctly
- [ ] Add saved guest with timeslot - timeslot persists after save
- [ ] Edit guest and change timeslot - change saves correctly
- [ ] Download voucher - includes timeslot info
- [ ] Book 2 activities - each activity has separate participant_time_slots
- [ ] Same participant in 2 activities - can have different timeslots
- [ ] Add accommodation (no timeslots) - no timeslot fields shown

## Frontend Checklist

- [ ] Timeslot dropdown only enabled when participant checkbox is checked
- [ ] Timeslot display shows: "Time Slot: HH:MM - HH:MM"
- [ ] JavaScript uses `slot.timeslot_id` not `slot.id`
- [ ] Form submission includes `time_slot` in guest data
- [ ] Console logs are removed (cleaned up 2026-04-21)

## References

### Database Migrations
- `database/migrations/2026_04_20_000001_add_participant_time_slots_to_activity_bookings.php`

### Key Files Modified (2026-04-21)
- [resources/views/frontend/traveler/manage-guests.blade.php](resources/views/frontend/traveler/manage-guests.blade.php)
- [app/Http/Controllers/Frontend/TripController.php](app/Http/Controllers/Frontend/TripController.php)

### Controllers
- [app/Http/Controllers/Frontend/BookingController.php](app/Http/Controllers/Frontend/BookingController.php) - Checkout & booking creation
- [app/Http/Controllers/Frontend/TripController.php](app/Http/Controllers/Frontend/TripController.php) - Manage guests & voucher download

### Views
- [resources/views/frontend/checkout.blade.php](resources/views/frontend/checkout.blade.php) - Initial booking
- [resources/views/frontend/traveler/manage-guests.blade.php](resources/views/frontend/traveler/manage-guests.blade.php) - Manage guests
- [resources/views/frontend/traveler/trip-detail.blade.php](resources/views/frontend/traveler/trip-detail.blade.php) - Trip summary

## Future Enhancements

### Potential Improvements
1. UI: Show activity name next to timeslot when managing guests with multiple activities
2. Validation: Warn if participant timeslots overlap with other bookings
3. Reporting: Add timeslot info to booking reports
4. Export: Include timeslots in guest list export

### Notes
- Current system assumes one activity per booking
- Multiple activities = multiple separate bookings (which is correct)
- Each booking is independent with its own participant list
- Timeslot storage is efficient (single JSON column vs multiple table rows)
