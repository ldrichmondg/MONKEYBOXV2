<?php

namespace Database\Seeders;

use App\Models\Direccion;
use Illuminate\Database\Seeder;

class SeederDireccion extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $direccion = [
            'DIRECCION' => 'Paraíso',
            'TIPO' => 1,
            'CODIGOPOSTAL' => 10294,
            'IDCLIENTE' => 1,
            'PAISESTADO' => 'Cartago, Paraíso'
        ];
        $direccion2 = [
            'DIRECCION' => 'Tejar',
            'TIPO' => 2,
            'CODIGOPOSTAL' => 10294,
            'IDCLIENTE' => 1,
            'PAISESTADO' => 'Cartago, El Guarco'
        ];
        $direccion3 = [
            'DIRECCION' => 'Guadalupe',
            'TIPO' => 1,
            'CODIGOPOSTAL' => 30106,
            'IDCLIENTE' => 2,
            'PAISESTADO' => 'Cartago, Guadalupe'
        ];

        Direccion::create($direccion);
        Direccion::create($direccion2);
        Direccion::create($direccion3);
    }
}
