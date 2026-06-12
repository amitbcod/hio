<?php

namespace App\Services;

use App\Mail\BookingStatusChanged;
use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OperatorBookingNotificationService
{
    public function notifyBookingStatusChanged(Model $booking, string $bookingType, string $status): void
    {
        $customerEmail = $this->getValidEmail($booking->guest_email ?? null);

        if ($customerEmail) {
            try {
                Mail::to($customerEmail)->send(new BookingStatusChanged($booking, $bookingType, $status, 'customer'));
            } catch (\Throwable $e) {
                Log::error('Failed to send booking status email to customer', [
                    'booking_id' => $booking->id,
                    'booking_type' => $bookingType,
                    'status' => $status,
                    'email' => $customerEmail,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        foreach ($this->getAdminEmails() as $adminEmail) {
            try {
                Mail::to($adminEmail)->send(new BookingStatusChanged($booking, $bookingType, $status, 'admin'));
            } catch (\Throwable $e) {
                Log::error('Failed to send booking status email to admin', [
                    'booking_id' => $booking->id,
                    'booking_type' => $bookingType,
                    'status' => $status,
                    'email' => $adminEmail,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function getAdminEmails(): array
    {
        $adminEmails = AdminUser::where('status', 'active')
            ->pluck('email')
            ->filter()
            ->unique()
            ->toArray();

        $adminFrom = config('mail.from.address');

        if ($adminFrom && $this->getValidEmail($adminFrom)) {
            $adminEmails[] = $adminFrom;
        }

        return array_values(array_unique($adminEmails));
    }

    private function getValidEmail(?string $email): ?string
    {
        if (empty($email)) {
            return null;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
}
