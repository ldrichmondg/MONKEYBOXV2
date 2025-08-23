<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailCumpleanios extends Mailable
{
    use Queueable, SerializesModels;

    public $usuario;

    use Queueable, SerializesModels;

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Feliz cumpleaños de parte de MonkeyBox',
        );
    }

    /**
     * Create a new message instance.
     *
     * @param  object  $tracking  Información del tracking eliminado.
     * @param  object  $usuario  Información del usuario asociado.
     */
    public function __construct($usuario)
    {
        $this->usuario = $usuario;
    }

    public function content(): Content
    {
        $fechaVencimiento = Carbon::now()->addDays(7)->format('d/m/Y'); // o 'Y-m-d' si preferís
        $codigoDescuento = 'FELIZ'.Carbon::now()->format('mY'); // genera FELIZ042025

        return new Content(
            markdown: 'mail.cumpleanios',
            with: [
                'usuario' => $this->usuario,
                'fechaVencimiento' => $fechaVencimiento,
                'codigoDescuento' => $codigoDescuento,
            ],
        );
    }
}
