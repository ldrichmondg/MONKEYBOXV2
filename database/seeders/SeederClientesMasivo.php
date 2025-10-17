<?php

namespace Database\Seeders;

use App\Models\Enum\TipoPerfiles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use League\Csv\Reader;

class SeederClientesMasivo extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $csv = Reader::createFromPath(base_path('resources/data/importacionClientesMasivos.csv'), 'r');
        $csv->setHeaderOffset(0);

        $rows = iterator_to_array($csv); // 👈 Leer CSV solo una vez

        $usersData = [];
        $clientesData = [];
        $direccionesData = [];

        // 1. Preparar datos
        $password = Hash::make('temporal123'); // 👈 una sola vez
        $index = 0;
        foreach ($rows as $row) {

            if (strlen($row['TELEFONO']) > 15) {
                Log::warning("TELEFONO demasiado largo en fila: " . ($index) . " | " . $row['TELEFONO']);
            }

            $usersData[] = [
                'NOMBRE'     => $row['NOMBRE'],
                'APELLIDOS'  => $row['APELLIDOS'],
                'EMPRESA'    => $row['EMPRESA'],
                'CEDULA'     => $row['CEDULA'] ?: null,
                'TELEFONO'   => $row['TELEFONO'] ?: null,
                'email'      => $row['email'] ?: null,
                'IDPERFIL'   => TipoPerfiles::Clientes,
                'password'   => $password,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $index++;
            //Log::info($index. ' '. json_encode($row));
        }

        $lastUserIdBefore = DB::table('users')->max('id') ?? 0;
        DB::transaction(function () use ($usersData, $rows, $now, $lastUserIdBefore, &$clientesData, &$direccionesData) {
            // 2. Insertar usuarios
            $chuckIndex = 0;
            foreach (array_chunk($usersData, 500) as $chunk) {
                $chuckIndex++;
                Log::info('Chunk# '. $chuckIndex);
                DB::table('users')->insert($chunk);
            }

            // Calcular IDs asignados
            $firstId = $lastUserIdBefore + 1;
            $userIds = range($firstId, $firstId + count($usersData) - 1);

            $index = 0;
            $rows = array_values($rows);
            // 3. Clientes
            foreach ($rows as $i => $row) {

                $clientesData[] = [
                    'CASILLERO'  => $row['CASILLERO'],
                    'IDUSUARIO'  => $userIds[$index],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $index++;
            }

            $lastClienteIdBefore = DB::table('cliente')->max('id') ?? 0;
            foreach (array_chunk($clientesData, 500) as $chunk) {
                DB::table('cliente')->insert($chunk);
            }

            $firstClienteId = $lastClienteIdBefore + 1;
            $clienteIds = range($firstClienteId, $firstClienteId + count($clientesData) - 1);

            // 4. Direcciones
            foreach ($rows as $i => $row) {
                $direccionesData[] = [
                    'DIRECCION'    => $row['DIRECCION'],
                    'TIPO'         => $row['TIPO'],
                    'CODIGOPOSTAL' => $row['CODIGOPOSTAL'],
                    'PAISESTADO'   => $row['PAISESTADO'],
                    'IDCLIENTE'    => $clienteIds[$i],
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }

            foreach (array_chunk($direccionesData, 500) as $chunk) {
                DB::table('direccion')->insert($chunk);
            }
        });

    }
}
