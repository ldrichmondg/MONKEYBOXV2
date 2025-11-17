<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Direccion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SeederClienteNoAsignado extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear el usuario, cliente y direccion
        $user =
            [
                'NOMBRE' => 'Usuario',
                'APELLIDOS' => 'NoAsignado',
                'CEDULA' => 222222222,
                'email' => 'usuario.noasignado@monkeyboxcr.com',
                'password' => Hash::make('MonkeyBox123+'),
                'TELEFONO' => 22222222,
                'email_verified_at' => Carbon::now(),
                'IDPERFIL' => 3,
            ];

        $usuarioObj = User::create($user);


        $cliente = [
            'CASILLERO' => '2454',
            'IDUSUARIO' => $usuarioObj->id,
            'FECHANACIMIENTO' => '2005-03-01',
        ];

        $clienteObj = Cliente::create($cliente);

        $direccion = [
            'DIRECCION' => 'NoAsignado',
            'TIPO' => 1,
            'CODIGOPOSTAL' => 10294,
            'IDCLIENTE' => $clienteObj->id,
            'PAISESTADO' => 'Cartago, Paraíso',
        ];

        Direccion::create($direccion);
    }
}
