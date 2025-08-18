<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
class EmailAvisoCostaRica extends Mailable
{
    use Queueable, SerializesModels;

    public $equivocado;
    public $tracking;
    public $usuario;
    use Queueable, SerializesModels;

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if ($this->equivocado) {
            return new Envelope(
                subject: 'Corrección de Notificación: Tu paquete llego a Costa Rica',
            );
        }
        return new Envelope(
            subject: 'Tu paquete llego a Costa Rica',
        );
    }

    /**
     * Create a new message instance.
     *
     * @param object $tracking Información del tracking eliminado.
     * @param object $usuario Información del usuario asociado.
     */
    public function __construct($usuario, $tracking, $equivocado = false)
    {
        $this->usuario = $usuario;
        $this->equivocado = $equivocado;
        $this->tracking = $tracking;
    }
    public function content(): Content
    {
        if ($this->equivocado) {
            return new Content(
                markdown: 'mail.aviso-equivocado-costarica',
                with:[
                    'tracking' => $this->tracking,
                    'usuario' => $this->usuario,
                ],
            );
        }

        return new Content(
            markdown: 'mail.aviso-costarica',
            with:[
                'tracking' => $this->tracking,
                'usuario' => $this->usuario,
            ]
        );
    }
    
}