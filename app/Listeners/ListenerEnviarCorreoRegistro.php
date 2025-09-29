<?php

namespace App\Listeners;

use App\Events\EventoRegistroUsuario;
use App\Mail\EmailRegistroUsuario;
use Exception;
use Illuminate\Support\Facades\Mail;

class ListenerEnviarCorreoRegistro
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
    public function handle(EventoRegistroUsuario $event): void
    {
        $usuario = $event->usuario;
        Mail::to($usuario->email)->send(new EmailRegistroUsuario($usuario));
    }
}
