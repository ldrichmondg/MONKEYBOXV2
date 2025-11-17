<?php

namespace App\Console\Commands;

use App\Services\Proveedores\Aeropost\ServicioAeropost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActualizarPrealertadosCodigo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:actualizar-prealertados-codigo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Actualizando prealerta codigo");
        $servicioAp = new ServicioAeropost();
        $servicioAp->SincronizarEncabezadoTrackings([]);
        Log::info("TERMINO Actualizando prealerta codigo");
    }
}
