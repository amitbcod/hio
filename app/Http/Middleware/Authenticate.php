<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // Redirect to operator login if the URL starts with /operator
        if ($request->is('operator/*')) {
            return route('operator.login');
        }
        // Default fallback (if you have other user types)
        return route('login');
    }
}
