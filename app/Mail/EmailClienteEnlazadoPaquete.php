<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailClienteEnlazadoPaquete extends Mailable
{
    use Queueable, SerializesModels;
    public $usuario;
    public $tracking;
    public $equivocado;

    public function __construct($tracking,$usuario, $equivocado = false)
    {
        $this->tracking = $tracking;
        $this->usuario = $usuario;
        $this->equivocado = $equivocado;
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Email Cliente Enlazado Paquete',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        try{
            if ($this->equivocado) {
                return new Content(
                    markdown: 'mail.paqueteEnlazado-equivocado',
                    with:[
                        'usuario' => $this->usuario,
                        'tracking' => $this->tracking,

                    ],
                );
            }
            return new Content(
                markdown: 'mail.paqueteEnlazado',
                with:[
                    'tracking' => $this->tracking,
                    'usuario' => $this->usuario,
                            
                ],
            );
        }catch(\Exception $e){
            var_dump($e);
            die();
        }
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
