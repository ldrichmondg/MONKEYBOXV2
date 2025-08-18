<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventoClienteEnlazadoPaquete
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $usuario;
    public $tracking;
    public $equivocado;

    /**
     * Create a new event instance.
     */
    public function __construct($tracking,$usuario,$equivocado = false)
    {
        $this->tracking = $tracking;
        $this->usuario = $usuario;
        $this->equivocado = $equivocado;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
