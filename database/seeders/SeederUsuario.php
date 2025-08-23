<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SeederUsuario extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ver los campos que debe tener la migracion
        $users = [
            [
                'NOMBRE' => 'Jairo',
                'APELLIDOS' => 'Saborio',
                'CEDULA' => 111111111,
                'email' => 'jairo.saborio@monkeyboxcr.com',
                'password' => Hash::make('MonkeyBox123+'),
                'TELEFONO' => 70800926,
                'email_verified_at' => Carbon::now(),
                'IDPERFIL' => 1,
            ],
            [
                'NOMBRE' => 'Admin',
                'APELLIDOS' => 'Admin',
                'CEDULA' => 123456789,
                'email' => 'luis.richmond@zeitwisecr.com',
                'password' => Hash::make('MBFenix123+'),
                'TELEFONO' => 70800926,
                'email_verified_at' => Carbon::now(),
                'IDPERFIL' => 1,
            ],
            [
                'NOMBRE' => 'Usuario',
                'APELLIDOS' => 'Prueba #1',
                'CEDULA' => 304958273,
                'email' => 'empleado@gmail.com',
                'password' => Hash::make('Password123'),
                'TELEFONO' => 214124456,
                'email_verified_at' => Carbon::now(),
                'IDPERFIL' => 2,
            ],
            [
                'NOMBRE' => 'Cliente',
                'APELLIDOS' => 'Prueba #1',
                'CEDULA' => 999999999,
                'email' => 'luis.richmondg@gmail.com',
                'password' => Hash::make('MB#RichmondFenix123'),
                'TELEFONO' => 84861987,
                'email_verified_at' => Carbon::now(),
                'IDPERFIL' => 3,
            ],
            [
                'NOMBRE' => 'Cliente',
                'APELLIDOS' => 'Prueba #2',
                'CEDULA' => 888888888,
                'email' => 'otrocorreo@gmail.com',
                'password' => Hash::make('MB#RichmondFenix123'),
                'TELEFONO' => 84861987,
                'email_verified_at' => Carbon::now(),
                'IDPERFIL' => 3,
            ],

        ];

        foreach ($users as $usuario) {
            User::create($usuario);
        }

    }
}
