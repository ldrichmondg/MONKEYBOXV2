<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnviarFactura extends Mailable
{
    use Queueable, SerializesModels;

    public $usuario;
    public $tracking;
    public $equivocado;

    /**
     * Create a new message instance.
     */
    public function __construct($usuario, $tracking, $equivocado)
    {
        $this->usuario = $usuario;
        $this->tracking = $tracking;
        $this->equivocado = $equivocado;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        if($this->equivocado){
            return $this->markdown('mail.email-factura-equivocado')
                                ->subject('Factura enviada por error');
        }
        return $this->markdown('mail.email-factura')
                    ->subject('Factura enviada correctamente')
                    ->attach(storage_path('app/public/facturas/' . $this->tracking->IDTRACKING.".pdf"));
    }
}
