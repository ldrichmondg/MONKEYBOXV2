<?php

namespace App\Listeners;

use App\Events\EventoClienteEnlazadoPaquete;
use App\Mail\EmailClienteEnlazadoPaquete;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class ListenerEnviarCorreoEnlazadoPaquete implements ShouldQueue
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

        $usuario = $event->usuario;
        $tracking = $event->tracking;
        $equivocado = $event->equivocado ?? false;

        //Mail::to($usuario->email)->send(new EmailClienteEnlazadoPaquete($tracking, $usuario, $equivocado));
    }
}
