<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SeederPerfil::class,
            SeederUsuario::class,
            SeederCliente::class,
            SeederDireccion::class,
            SeederEstadoMBox::class,
            SeederProveedor::class,
            // NO AGREGAR SEEDERREALES POR NINGUN MOTIVO AH NO SER QUE SE DESEE ALIMENTAR TODOS LOS CLIENTES DESDE CERO
            // PONER SEEDERCLIENTES REALES REPRESENTA UN PELIGRO PARA LA BD
        ]
        );

    }
}
