<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Tracking;
use App\Models\TrackingProveedor;
//use App\Services\ServicioAeropost;
use App\Models\User;
use App\Services\Proveedores\Aeropost\ServicioAeropost;
use App\Services\ServicioTracking;
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
        $servicioAp = new ServicioAeropost();
        $servicioAp->SincronizarCompletoTrackings([]);
    }

}
