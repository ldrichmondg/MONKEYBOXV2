<?php

namespace Database\Seeders;

use App\Models\Perfil;
use Illuminate\Database\Seeder;

class SeederPerfil extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perfiles = [
            ['DESCRIPCION' => 'Administrador'],
            ['DESCRIPCION' => 'Trabajador'],
            ['DESCRIPCION' => 'Cliente'],
        ];
        foreach ($perfiles as $perfil) {
            Perfil::create($perfil);
        }
    }
}
