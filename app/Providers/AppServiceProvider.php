<?php

namespace App\Providers;


use App\Exceptions\EnumCodigosAppError;
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
        Response::macro('error', function ($message, $titleMessage = 'Error', $code = 500, $errorApp = EnumCodigosAppError::ERROR_INTERNO) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
                'titleMessage' => $titleMessage,
                'errorApp' => $errorApp,
            ], $code);
        });
    }
}
