<?php

namespace App\Listeners;

use App\Events\EventoCorreoEliminarTracking;
use App\Mail\EmailEliminacionTracking;
use Exception;
use Illuminate\Support\Facades\Mail;

class ListenerEnviarCorreoEliminaTracking
{
    /**
     * Handle the event.
     */
    public function handle(EventoCorreoEliminarTracking $evento): bool
    {
        try {
            Mail::to($evento->usuario->email)->send(new EmailEliminacionTracking($evento->tracking, $evento->usuario));

            return true; // allow event propagation
        } catch (Exception $e) {
            // Handle the exception
            return false; // stop event propagation
        }
    }
}
