<?php
namespace App\Listeners;

use App\Mail\EnviarFactura;
use Illuminate\Support\Facades\Mail;
use App\Events\EventoFacturaGenerada;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Direccion;


class ListenerFacturaGenerada
{
    public function handle(EventoFacturaGenerada $event): bool
    {
        try {
            Log::info('ListenerFacturaGenerada ejecutado');
            $usuario = $this->ObtenerCliente($event->tracking);

            Mail::to($usuario->email)->send(
                new EnviarFactura($usuario, $event->tracking, $event->equivocado)
            );
            return true;
        } catch (Exception $e) {
            Log::error('Error en ListenerFacturaGenerada: ' . $e->getMessage());
            return false;
        }
    }

    private function ObtenerCliente($tracking)
    {
        
        $idDireccion = $tracking->IDDIRECCION;
        $direccion = Direccion::where('ID', $idDireccion)->first();
        //$direccionCliente = $tracking->direccion()->first();
        $cliente = $direccion->cliente()->first();
        return $cliente->usuario()->first();
    }
}
