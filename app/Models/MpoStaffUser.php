<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class MpoStaffUser extends Authenticatable implements CanResetPasswordContract
{
    use CanResetPassword;
    use Notifiable;

    protected $table = 'mpo_staff_users';

    protected $guarded = [];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'account_reset_required' => 'boolean',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\MpoStaffPasswordReset($token));
    }

    public function mpo(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    public function permissionOverrides(): HasMany
    {
        return $this->hasMany(MpoStaffPermissionOverride::class);
    }

    public function accountingConfigurations(): HasMany
    {
        return $this->hasMany(MpoAccountingConfiguration::class, 'responsible_accountant_user_id');
    }
}
