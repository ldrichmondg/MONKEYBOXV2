<?php

namespace App\Jobs;


use App\Services\Proveedores\Aeropost\ServicioAeropost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class JobProcesarTrackingsAp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public bool $soloAyer)
    {
        $this->onQueue('aeropost');
    }

    public function handle()
    {
        Log::info("Iniciando sincronización pesada...");

        $servicioAp = new ServicioAeropost();
        $servicioAp->SincronizarCompletoTrackings([], $this->soloAyer);

        Log::info("Finalizando sincronización pesada.");
    }
}
