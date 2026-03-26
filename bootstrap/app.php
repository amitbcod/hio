<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('traveler/*') || $request->is('booking/*')) {
                return route('traveler.login');
            }

            if ($request->is('operator/*')) {
                return route('operator.login');
            }

            return route('login');
        });
        
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'csrf' => \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
