<?php

namespace App\Listeners;

use App\Events\EventoClienteEnlazadoPaquete;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailClienteEnlazadoPaquete;
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

            Mail::to($usuario->email)->send(new EmailClienteEnlazadoPaquete($tracking,$usuario,$equivocado));
            return true; // allow event propagation
        
        }catch (Exception $e) {
            // var_dump($e);
            // die($e);
            return false; //stop event propagation
        }
    }
}
