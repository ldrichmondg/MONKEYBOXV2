<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
class EmailEliminacionTracking extends Mailable
{
    use Queueable, SerializesModels;

    public $tracking;
    public $usuario;
    use Queueable, SerializesModels;

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Eliminación de tracking',
        );
    }

    /**
     * Create a new message instance.
     *
     * @param object $tracking Información del tracking eliminado.
     * @param object $usuario Información del usuario asociado.
     */
    public function __construct($tracking, $usuario)
    {
        $this->tracking = $tracking;
        $this->usuario = $usuario;
    }
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.eliminacion-tracking',
            with:[
                'tracking' => $this->tracking,
                'usuario' => $this->usuario,
            ],
        );
    }
    
}