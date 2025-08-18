<?php

namespace App\Listeners;

use App\Events\EventoRegistroUsuario;
use App\Mail\EmailRegistroUsuario;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
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
    public function handle(EventoRegistroUsuario $event): bool
    {
        try {
            $usuario = $event->usuario;

            Mail::to($usuario->email)->send(new EmailRegistroUsuario($usuario));
            return true; // allow event propagation
        
        }catch (Exception $e) {
            //Handle the exception
            return false; //stop event propagation
        }
           
        
    }
}
