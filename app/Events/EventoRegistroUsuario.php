<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventoRegistroUsuario
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $usuario;

    /**
     * Create a new event instance.
     */
    public function __construct(User $usuario, string $passwordSinEncriptar)
    {
        $usuario->tempPassword = $passwordSinEncriptar; // Porque ya la password ya viene con hash en el modelo
        $this->usuario = $usuario;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    // public function broadcastOn()#: array
    // {
    /*
    return [
        new PrivateChannel('channel-name'),
    ];
    */
    // }
}
