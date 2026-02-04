<?php

namespace Spatie\Permission\Traits;

if (!trait_exists('\Spatie\Permission\Traits\HasRoles')) {
    trait HasRoles {
        // Minimal compatibility methods to avoid fatal errors when package not installed in local env.
        public function assignRole($role) { return; }
        public function syncPermissions($perms) { return; }
        public function permissions() { return collect(); }
        public function hasPermissionTo($perm) { return false; }
    }
}
