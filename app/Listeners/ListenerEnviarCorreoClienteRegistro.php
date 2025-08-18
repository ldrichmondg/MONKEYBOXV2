<?php

namespace App\Listeners;

use App\Events\EventoRegistroCliente;
use App\Mail\EmailRegistroCliente;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class ListenerEnviarCorreoClienteRegistro
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
    public function handle(EventoRegistroCliente $event): bool
    {
        try {
            $cliente = $event->cliente;
            $usuario = $cliente->usuario;

            Mail::to($usuario->email)->send(new EmailRegistroCliente($cliente, $usuario));
            return true; // allow event propagation
        
        }catch (Exception $e) {
            //Handle the exception
            return false; //stop event propagation
        }
    }
}
