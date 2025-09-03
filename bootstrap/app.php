<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->respond(function ($response) {
            if ($response->getStatusCode() === 419) {
                $request = request();

                // cerramos la sesion para que lo diriga al login. Si no se cierra la sesion, lo manda al dashboard y pueden haber confusiones
                auth()->logout(); // cerrar sesión
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        "status" => "error",
                        'message' => 'La página expiró. Inténtelo de nuevo.',
                        'redirect' => route('login'),
                    ],419);
                }

                return back()
                    ->with('error', 'Tu sesión expiró, por favor inicia sesión de nuevo.');
            }
            elseif ($response->getStatusCode() === 401) {
                $request = request();

                // cerramos la sesion para que lo diriga al login. Si no se cierra la sesion, lo manda al dashboard y pueden haber confusiones
                auth()->logout(); // cerrar sesión
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        "status" => "error",
                        'message' => 'No tienes acceso al recurso que intentaste acceder, inicia sesión de nuevo.',
                        'redirect' => route('login'),
                    ],401);
                }

                return redirect()->route('login')
                    ->with('error', 'No tienes acceso al recurso que intentaste acceder, inicia sesión de nuevo.');
            }

            return $response;
        });

    })->create();
