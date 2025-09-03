<?php

namespace App\Providers;


use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // - Se usa para estandarizar los errores que su respuesta es unicamente JSON
        Response::macro('error', function ($message, $titleMessage = 'Error', $code = 500) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
                'titleMessage' => $titleMessage,
            ], $code);
        });
    }
}
