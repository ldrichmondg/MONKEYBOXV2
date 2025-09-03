<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PintTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pint:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta Pint en modo test (revisión)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        passthru('php vendor/bin/pint --test');
    }
}
