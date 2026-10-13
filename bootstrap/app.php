<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // ============================================================
        // 419 Page Expired → redirigir al login automáticamente
        // ============================================================
        $exceptions->render(function (TokenMismatchException $e, Request $request) {

            // Si la petición espera JSON (fetch, axios, ajax)
            if ($request->expectsJson()) {
                return response()->json([
                    'error'   => 'Sesión expirada',
                    'message' => 'Tu sesión ha caducado. Inicia sesión nuevamente.',
                    'redirect' => route('login'),
                ], 401);
            }

            // Navegación normal del navegador → redirigir al login
            return redirect()
                ->route('login')
                ->with('error', 'Tu sesión ha caducado. Inicia sesión nuevamente.');
        });
    })
    ->create();