<?php

namespace Database\Seeders;

use App\Models\EstadoMBox;
use Illuminate\Database\Seeder;

class SeederEstadoMBox extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [

            [
                'DESCRIPCION' => 'Sin Registrar',
                'COLORCLASS' => 'bg-transparent border-gray-200 text-gray-200',
                'ORDEN' => -1
            ],
            [
                'DESCRIPCION' => 'No se encontró',
                'COLORCLASS' => 'bg-transparent border-red-400 text-red-400',
                'ORDEN' => -2
            ],
            // aqui ya van los estados fijos para monkey
            [
                'DESCRIPCION' => 'Sin Prealertar',
                'COLORCLASS' => 'bg-transparent border-pink-300 text-pink-300',
                'ORDEN' => 1
            ],
            [
                'DESCRIPCION' => 'Prealertado',
                'COLORCLASS' => 'bg-transparent border-yellow-400 text-yellow-400',
                'ORDEN' => 2
            ],
            [
                'DESCRIPCION' => 'Paquete Pérdido',
                'COLORCLASS' => 'bg-transparent border-red-400 text-red-400',
                'ORDEN' => 20
            ],
            [
                'DESCRIPCION' => 'Recibido Miami',
                'COLORCLASS' => 'bg-transparent border-sky-400 text-sky-400',
                'ORDEN' => 3
            ],
            [
                'DESCRIPCION' => 'Tránsito a CR',
                'COLORCLASS' => 'bg-transparent border-blue-400 text-blue-400',
                'ORDEN' => 4
            ],
            [
                'DESCRIPCION' => 'Proceso Aduanas',
                'COLORCLASS' => 'bg-transparent border-gray-400 text-gray-400',
                'ORDEN' => 5
            ],
            [
                'DESCRIPCION' => 'Oficinas MB',
                'COLORCLASS' => 'bg-transparent border-brown-400 text-brown-400',
                'ORDEN' => 6
            ],
            [
                'DESCRIPCION' => 'Entregado',
                'COLORCLASS' => 'bg-transparent border-purple-300 text-purple-300',
                'ORDEN' => 7
            ],
            [
                'DESCRIPCION' => 'Facturado',
                'COLORCLASS' => 'bg-transparent border-green-400 text-green-400',
                'ORDEN' => 8
            ],
            [
                'DESCRIPCION' => 'Eliminado',
                'COLORCLASS' => 'bg-transparent border-brown-400 text-brown-400',
                'ORDEN' => 25
            ],
        ];

        foreach ($estados as $estado) {

            EstadoMBox::create($estado);
        }
    }
}
