<?php

namespace App\Events;

use App\Models\Cliente;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventoRegistroCliente
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $cliente;

    public function __construct(Cliente $cliente, string $passwordSinEncriptar)
    {
        $cliente->tempPassword = $passwordSinEncriptar; // Porque ya la password ya viene con hash en el modelo
        $this->cliente = $cliente;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    /*public function broadcastOn()#: array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }*/
}
