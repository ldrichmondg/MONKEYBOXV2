<?php

namespace App\Console\Commands;

use App\Models\Tracking;
use App\Models\TrackingProveedor;
use App\Services\ServicioAeropost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcesarTrackingsAeropost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:procesar-tracking-aeropost';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle()
    {
        Log::info("**** Procesando COMANDO SINCRONIZACION ESTADOS DE AEROPOST ****");
        // Obtenemos todos los IDTRACKING del proveedor Aeropost
        $trackingProveedorIds = TrackingProveedor::where('IDPROVEEDOR', 1)
            ->pluck('IDTRACKING');

        // Filtramos listadoPendientes directamente en la consulta
            $listadoPendientes = Tracking::where('ESTADOSINCRONIZADO', 'Prealertado')->orWhere('ESTADOSINCRONIZADO', 'Recibido Miami')
            ->orWhere('ESTADOSINCRONIZADO', 'Tránsito a CR') ->orWhere('ESTADOSINCRONIZADO', 'Proceso Aduanas') ->orWhere('ESTADOSINCRONIZADO', 'Oficinas MB')
            ->whereIn('id', $trackingProveedorIds)
            ->pluck('IDTRACKING');

        Log::info('CMD: Listas trackings: ' . json_encode($listadoPendientes));
        ServicioAeropost::ProcesarTrackingsAeropost($listadoPendientes->toArray());
    }

}
