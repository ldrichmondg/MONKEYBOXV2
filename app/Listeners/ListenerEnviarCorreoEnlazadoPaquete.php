<?php

namespace App\Listeners;

use App\Events\EventoClienteEnlazadoPaquete;
use App\Mail\EmailClienteEnlazadoPaquete;
use Exception;
use Illuminate\Support\Facades\Mail;

class ListenerEnviarCorreoEnlazadoPaquete
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EventoClienteEnlazadoPaquete $event): bool
    {
        try {
            $usuario = $event->usuario;
            $tracking = $event->tracking;
            $equivocado = $event->equivocado ?? false;

            Mail::to($usuario->email)->send(new EmailClienteEnlazadoPaquete($tracking, $usuario, $equivocado));

            return true; // allow event propagation

        } catch (Exception $e) {
            // var_dump($e);
            // die($e);
            return false; // stop event propagation
        }
    }
}
