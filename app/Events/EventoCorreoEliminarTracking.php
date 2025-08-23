<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventoCorreoEliminarTracking
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tracking;

    public $usuario;

    /**
     * Create a new event instance.
     */
    public function __construct(
        $tracking,
        $usuario
    ) {
        $this->tracking = $tracking;
        $this->usuario = $usuario;
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
