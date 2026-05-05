<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestOtpToken extends Model
{
    protected $table = 'guest_otp_tokens';
    protected $guarded = [];
    public $timestamps = true;

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(AccommodationBooking::class, 'booking_id');
    }

    /**
     * Generate a random OTP code (6 digits)
     */
    public static function generateOtpCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create OTP token for guest email
     */
    public static function createForGuest(string $email, ?int $bookingId = null): self
    {
        // Delete expired tokens for this email
        self::where('email', $email)
            ->where('expires_at', '<', now())
            ->delete();

        $otp = self::create([
            'email' => $email,
            'otp_code' => self::generateOtpCode(),
            'booking_id' => $bookingId,
            'is_verified' => false,
            'expires_at' => now()->addMinutes(15), // OTP valid for 15 minutes
        ]);

        return $otp;
    }

    /**
     * Verify OTP token
     */
    public function verify(): bool
    {
        if ($this->is_verified) {
            return false; // Already verified
        }

        if ($this->expires_at < now()) {
            return false; // Expired
        }

        $this->is_verified = true;
        $this->verified_at = now();
        $this->save();

        return true;
    }

    /**
     * Check if token is valid and not expired
     */
    public function isValid(): bool
    {
        return !$this->is_verified && $this->expires_at > now();
    }
}
