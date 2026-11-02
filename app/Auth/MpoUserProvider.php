<?php

namespace App\Auth;

use App\Models\AdminUser;
use Illuminate\Auth\EloquentUserProvider;

class MpoUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier)
    {
        if (is_string($identifier) && preg_match('/^admin:(\d+)$/', $identifier, $matches)) {
            return AdminUser::query()->find((int) $matches[1]);
        }

        return parent::retrieveById($identifier);
    }
}
