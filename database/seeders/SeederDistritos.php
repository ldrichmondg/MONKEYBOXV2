<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class SeederDistritos extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $now = Carbon::now();

        // Ruta del archivo CSV
        $csv = Reader::createFromPath(base_path('resources/data/distritos.csv'), 'r');
        $csv->setHeaderOffset(0); // Primera fila como encabezado

        $batchSize = 100;
        $batch = [];

        foreach ($csv as $row) {
            $batch[] = [
                'IDCANTON' => $row['IDCANTON'],
                'NOMBRE' => $row['DISTRITO'],
                'CODIGOPOSTAL' => $row['CODIGOPOSTAL'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) === $batchSize) {
                DB::table('distritos')->insert($batch);
                $batch = []; // vaciamos el array
            }
        }

        // Insertar los restantes si los hay
        if (!empty($batch)) {
            DB::table('distritos')->insert($batch);
        }
    }
}
