<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function getAdminCompanyData(): array
    {
        $admin = null;
        if (session('admin_id')) {
            $admin = AdminUser::find(session('admin_id'));
        }

        if (!$admin) {
            $admin = AdminUser::where('status', 'active')
                ->orderByRaw("FIELD(role, 'super_admin', 'admin')")
                ->first();
        }

        $default = [
            'business_name' => 'LRT Mauritius LTD',
            'business_address' => 'Your Local Connection - Mauritius',
            'business_email' => 'info@lrt.mu',
            'business_phone' => '+230 1234 5678',
            'vat_number' => '12345678',
            'brn_number' => 'C12345678',
            'logo_path' => '',
        ];

        if (!$admin) {
            return $default;
        }

        $logoPath = $admin->logo_path ? ltrim($admin->logo_path, '/') : '';
        if ($logoPath) {
            $absoluteLogo = public_path($logoPath);
            if (file_exists($absoluteLogo)) {
                $logoPath = $absoluteLogo;
            } else {
                $logoPath = '';
            }
        }

        return [
            'business_name' => $admin->business_name ?: $default['business_name'],
            'business_address' => $admin->business_address ?: $default['business_address'],
            'business_email' => $admin->email ?: $default['business_email'],
            'business_phone' => $admin->phone_number ?: $default['business_phone'],
            'vat_number' => $admin->vat_number ?: $default['vat_number'],
            'brn_number' => $admin->brn_number ?: $default['brn_number'],
            'logo_path' => $logoPath,
        ];
    }

    protected function renderAdminCompanyLogoHtml(string $logoPath, string $fallbackName = 'Holidays.io'): string
    {
        if ($logoPath && file_exists($logoPath)) {
            return '<img src="' . $logoPath . '" width="100" height="40" style="width:100px; height:auto; display:block;" alt="' . e($fallbackName) . '">';
        }

        return '<div style="font-size:18px;font-weight:700;color:#f7971e;">' . e($fallbackName) . '</div>';
    }
}
