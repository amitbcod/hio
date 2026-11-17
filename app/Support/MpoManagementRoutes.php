<?php

namespace App\Support;

class MpoManagementRoutes
{
    public static function name(string $adminRouteName): string
    {
        if (request()->routeIs('mpo.groups.*', 'mpo.packages.*')
            && str_starts_with($adminRouteName, 'admin.')) {
            return 'mpo.'.substr($adminRouteName, strlen('admin.'));
        }

        return $adminRouteName;
    }
}
