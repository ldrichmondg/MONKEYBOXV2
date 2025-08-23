<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventoFacturaGenerada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tracking;

    public $equivocado;

    /**
     * Create a new event instance.
     */
    public function __construct(
        $tracking,
        $equivocado
    ) {
        $this->tracking = $tracking;
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
