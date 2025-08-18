<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeederProveedor extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proveedores = [
            [
                'NOMBRE' => 'Aeropost'
            ],
            [
                'NOMBRE' => 'MiLocker'
            ]
        ];


        foreach($proveedores as $proveedor){
            Proveedor::create($proveedor);
        }
    }
}
