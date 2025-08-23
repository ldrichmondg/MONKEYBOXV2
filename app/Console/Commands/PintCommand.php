<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PintCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pint';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta Pint para arreglar el código';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        passthru('php vendor/bin/pint');
    }
}
