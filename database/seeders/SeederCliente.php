<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class SeederCliente extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cliente = [
            'CASILLERO' => '2454',
            'IDUSUARIO' => 4,
            'FECHANACIMIENTO' => '2005-03-01',
        ];

        Cliente::create($cliente);

        $cliente2 = [
            'CASILLERO' => '222',
            'IDUSUARIO' => 5,
            'FECHANACIMIENTO' => '2005-04-18',
        ];

        Cliente::create($cliente2);

    }
}
