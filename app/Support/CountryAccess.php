<?php

namespace App\Support;

use App\Models\AdminUser;
use Illuminate\Support\Facades\DB;

class CountryAccess
{
    private static array $admins = [];

    public static function clearCache(): void
    {
        self::$admins = [];
    }

    public static function isMpoAdmin(): bool
    {
        $mpoAdmin = request()->attributes->get('mpo_admin');
        if ($mpoAdmin instanceof AdminUser) {
            return $mpoAdmin->admin_type === 'mpo';
        }

        return request()->is('admin/*') && self::admin()['admin_type'] === 'mpo';
    }

    public static function countryId(): ?int
    {
        $mpoAdmin = request()->attributes->get('mpo_admin');
        if ($mpoAdmin instanceof AdminUser && $mpoAdmin->admin_type === 'mpo') {
            return $mpoAdmin->country_id === null ? null : (int) $mpoAdmin->country_id;
        }

        $admin = self::admin();

        return $admin['admin_type'] === 'mpo' && $admin['country_id'] !== null
            ? (int) $admin['country_id']
            : null;
    }

    public static function isGlobalAdmin(): bool
    {
        return ! self::isMpoAdmin();
    }

    private static function admin(): array
    {
        $adminId = session('admin_id');
        if (! $adminId) {
            return ['admin_type' => 'global', 'country_id' => null];
        }

        if (array_key_exists((string) $adminId, self::$admins)) {
            return self::$admins[(string) $adminId];
        }

        $admin = DB::table('admin_users')
            ->where('id', $adminId)
            ->first(['admin_type', 'country_id']);

        return self::$admins[(string) $adminId] = [
            'admin_type' => $admin?->admin_type === 'mpo' ? 'mpo' : 'global',
            'country_id' => $admin?->country_id === null ? null : (int) $admin->country_id,
        ];
    }
}
