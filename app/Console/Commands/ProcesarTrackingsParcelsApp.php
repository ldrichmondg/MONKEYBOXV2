<?php

namespace App\Console\Commands;

use App\Models\Tracking;
use App\Models\TrackingProveedor;
use App\Services\ServicioParcelsApp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcesarTrackingsParcelsApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:procesar-tracking-parcels-app';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle()
    {
        // Obtenemos todos los IDTRACKING del proveedor Aeropost 
        $trackingProveedorIds = TrackingProveedor::pluck('IDTRACKING');

        // Filtramos listadoPendientes directamente en la consulta
            $listadoPendientes = Tracking::where()->whereIn('id', $trackingProveedorIds)
            ->pluck('IDTRACKING');;

        ServicioParcelsApp::ProcesarTrackingsParcelsApp($listadoPendientes->toArray());
    }

}
