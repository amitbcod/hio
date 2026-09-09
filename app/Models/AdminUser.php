<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model
{
    protected $table = 'admin_users';
    protected $guarded = [];
    protected $casts = [
        'package_policy' => 'array',
        'group_policy' => 'array',
    ];
    public $timestamps = true;

    public static function defaultPackagePolicy(): array
    {
        $admin = self::query()->first();
        $policy = $admin?->package_policy ?? [];

        return is_array($policy) ? $policy : [];
    }

    public static function defaultGroupPolicy(): array
    {
        $admin = self::query()->first();
        $policy = $admin?->group_policy ?? [];

        return is_array($policy) ? $policy : [];
    }
}