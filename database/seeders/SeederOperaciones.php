<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SeederOperaciones extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuariosOperaciones = [
            [
                'NOMBRE' => 'Genesis',
                'APELLIDOS' => 'A',
                'CEDULA' => 55555555,
                'email' => 'operaciones@monkeyboxcr.com',
                'password' => Hash::make('TEMP123+'),
                'TELEFONO' => 99999999,
                'email_verified_at' => Carbon::now(),
                'IDPERFIL' => 2,
            ],
            [
                'NOMBRE' => 'Brandon',
                'APELLIDOS' => 'B',
                'CEDULA' => 666666666,
                'email' => 'servicioalcliente@monkeyboxcr.com',
                'password' => Hash::make('TEMP123+'),
                'TELEFONO' => 88888888,
                'email_verified_at' => Carbon::now(),
                'IDPERFIL' => 2,
            ],
        ];

        foreach ($usuariosOperaciones as $usuario) {
            User::create($usuario);
        }
    }
}
