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
        // Filtramos aquellos trackings donde su estado sincronizado sea SPR(1) o PDO(2)
        /*$listadoPendientes = Tracking::whereIn('ESTADOSINCRONIZADO',['Sin Prealertar','Prealertado'])
            ->pluck('IDTRACKING')
            ->toArray();

        $this->info("Total pendientes: " . count($listadoPendientes));

        foreach ($listadoPendientes as $idTracking) {
            $this->line("Tracking ID: {$idTracking}");
        }*/

        //ServicioParcelsApp::ProcesarTrackingsParcelsApp($listadoPendientes);
        ServicioParcelsApp::ProcesarTrackingsParcelsApp(['1Z093A4A0395574965']);
    }

}
