<?php

namespace App\Listeners;

use App\Events\EventoRegistroCliente;
use App\Mail\EmailRegistroCliente;
use Exception;
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
    public function handle(EventoRegistroCliente $event): void
    {

        $cliente = $event->cliente;
        $usuario = $cliente->usuario;

        Mail::to($usuario->email)->send(new EmailRegistroCliente($cliente, $usuario));
    }
}
