<?php
// bootstrap/app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
    // For API/JSON requests, don't redirect guests to a "login" route — return 401 JSON.
    // For web requests, keep the default (redirect to /login) — useful if we add a dashboard later.
    $middleware->redirectGuestsTo(function (Request $request) {
        return $request->is('api/*') || $request->expectsJson() ? null : '/login';
    });
})
    ->withExceptions(function (Exceptions $exceptions): void {
        // Render JSON for all API + JSON-accepting requests.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();