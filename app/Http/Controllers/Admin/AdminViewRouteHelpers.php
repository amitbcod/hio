<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Redirector;

if (!function_exists(__NAMESPACE__ . '\\mapAdminViewName')) {
    function mapAdminViewName($view)
    {
        if (!is_string($view)) {
            return $view;
        }

        if (str_starts_with($view, 'operator.accommodation.')) {
            return str_replace('operator.accommodation.', 'admin.accommodation.', $view);
        }

        if (str_starts_with($view, 'operator.activity.')) {
            return str_replace('operator.activity.', 'admin.activity.', $view);
        }

        return $view;
    }
}

if (!function_exists(__NAMESPACE__ . '\\mapAdminRouteName')) {
    function mapAdminRouteName($name)
    {
        if (!is_string($name)) {
            return $name;
        }

        if (str_starts_with($name, 'operator.accommodation.')) {
            return str_replace('operator.accommodation.', 'admin.accommodation.', $name);
        }

        if (str_starts_with($name, 'operator.activity.')) {
            return str_replace('operator.activity.', 'admin.activity.', $name);
        }

        return $name;
    }
}

if (!function_exists(__NAMESPACE__ . '\\view')) {
    function view($view = null, $data = [], $mergeData = [])
    {
        return \view(mapAdminViewName($view), $data, $mergeData);
    }
}

if (!function_exists(__NAMESPACE__ . '\\route')) {
    function route($name, $parameters = [], $absolute = true)
    {
        return \route(mapAdminRouteName($name), $parameters, $absolute);
    }
}

if (!function_exists(__NAMESPACE__ . '\\redirect')) {
    function redirect($to = null, $status = 302, $headers = [], $secure = null)
    {
        $redirector = \redirect($to, $status, $headers, $secure);
        return new AdminRedirector($redirector);
    }
}

if (!class_exists(__NAMESPACE__ . '\\AdminRedirector')) {
    class AdminRedirector
    {
        protected Redirector $redirector;

        public function __construct(Redirector $redirector)
        {
            $this->redirector = $redirector;
        }

        public function __call($method, $args)
        {
            if ($method === 'route' && isset($args[0]) && is_string($args[0])) {
                $args[0] = mapAdminRouteName($args[0]);
            }

            return $this->redirector->$method(...$args);
        }

        public function __get($name)
        {
            return $this->redirector->$name;
        }

        public function __set($name, $value)
        {
            $this->redirector->$name = $value;
        }

        public function __isset($name)
        {
            return isset($this->redirector->$name);
        }
    }
}
