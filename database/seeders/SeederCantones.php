<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeederCantones extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('cantones')->insert([
            // San José (IDPROVINCIA = 1)
            ['NOMBRE' => 'San José', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Escazú', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Desamparados', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Puriscal', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Tarrazú', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Aserrí', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Mora', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Goicoechea', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Santa Ana', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Alajuelita', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Vázquez de Coronado', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Acosta', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Tibás', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Moravia', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Montes de Oca', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Turrubares', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Dota', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Curridabat', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'Pérez Zeledón', 'IDPROVINCIA' => 1],
            ['NOMBRE' => 'León Cortés', 'IDPROVINCIA' => 1],

            // Alajuela (IDPROVINCIA = 2)
            ['NOMBRE' => 'Alajuela', 'IDPROVINCIA' => 2],
            ['NOMBRE' => 'San Ramón', 'IDPROVINCIA' => 2],
            ['NOMBRE' => 'Grecia', 'IDPROVINCIA' => 2],
            ['NOMBRE' => 'San Mateo', 'IDPROVINCIA' => 2],
            ['NOMBRE' => 'Atenas', 'IDPROVINCIA' => 2],
            ['NOMBRE' => 'Naranjo', 'IDPROVINCIA' => 2],
            ['NOMBRE' => 'Palmares', 'IDPROVINCIA' => 2],
            ['NOMBRE' => 'Poas', 'IDPROVINCIA' => 2],
            ['NOMBRE' => 'Orotina', 'IDPROVINCIA' => 2],
            ['NOMBRE' => 'San Carlos', 'IDPROVINCIA' => 2],
            ['NOMBRE' => 'Alfaro Ruiz', 'IDPROVINCIA' => 2],
            ['NOMBRE' => 'Valverde Vega', 'IDPROVINCIA' => 2],
            ['NOMBRE' => 'Upala', 'IDPROVINCIA' => 2],
            ['NOMBRE' => 'Los Chiles', 'IDPROVINCIA' => 2],
            ['NOMBRE' => 'Guatuso', 'IDPROVINCIA' => 2],

            // Cartago (IDPROVINCIA = 3)
            ['NOMBRE' => 'Cartago', 'IDPROVINCIA' => 3],
            ['NOMBRE' => 'Paraíso', 'IDPROVINCIA' => 3],
            ['NOMBRE' => 'La Unión', 'IDPROVINCIA' => 3],
            ['NOMBRE' => 'Jiménez', 'IDPROVINCIA' => 3],
            ['NOMBRE' => 'Turrialba', 'IDPROVINCIA' => 3],
            ['NOMBRE' => 'Alvarado', 'IDPROVINCIA' => 3],
            ['NOMBRE' => 'Oreamuno', 'IDPROVINCIA' => 3],
            ['NOMBRE' => 'El Guarco', 'IDPROVINCIA' => 3],

            // Heredia (IDPROVINCIA = 4)
            ['NOMBRE' => 'Heredia', 'IDPROVINCIA' => 4],
            ['NOMBRE' => 'Barva', 'IDPROVINCIA' => 4],
            ['NOMBRE' => 'Santo Domingo', 'IDPROVINCIA' => 4],
            ['NOMBRE' => 'Santa Barbara', 'IDPROVINCIA' => 4],
            ['NOMBRE' => 'San Rafael', 'IDPROVINCIA' => 4],
            ['NOMBRE' => 'San Isidro', 'IDPROVINCIA' => 4],
            ['NOMBRE' => 'Belén', 'IDPROVINCIA' => 4],
            ['NOMBRE' => 'Flores', 'IDPROVINCIA' => 4],
            ['NOMBRE' => 'San Pablo', 'IDPROVINCIA' => 4],
            ['NOMBRE' => 'Sarapiquí', 'IDPROVINCIA' => 4],

            // Guanacaste (IDPROVINCIA = 5)
            ['NOMBRE' => 'Liberia', 'IDPROVINCIA' => 5],
            ['NOMBRE' => 'Nicoya', 'IDPROVINCIA' => 5],
            ['NOMBRE' => 'Santa Cruz', 'IDPROVINCIA' => 5],
            ['NOMBRE' => 'Bagaces', 'IDPROVINCIA' => 5],
            ['NOMBRE' => 'Carrillo', 'IDPROVINCIA' => 5],
            ['NOMBRE' => 'Cañas', 'IDPROVINCIA' => 5],
            ['NOMBRE' => 'Abangares', 'IDPROVINCIA' => 5],
            ['NOMBRE' => 'Tilarán', 'IDPROVINCIA' => 5],
            ['NOMBRE' => 'Nandayure', 'IDPROVINCIA' => 5],
            ['NOMBRE' => 'La Cruz', 'IDPROVINCIA' => 5],
            ['NOMBRE' => 'Hojancha', 'IDPROVINCIA' => 5],

            // Puntarenas (IDPROVINCIA = 6)
            ['NOMBRE' => 'Puntarenas', 'IDPROVINCIA' => 6],
            ['NOMBRE' => 'Esparza', 'IDPROVINCIA' => 6],
            ['NOMBRE' => 'Buenos Aires', 'IDPROVINCIA' => 6],
            ['NOMBRE' => 'Montes de Oro', 'IDPROVINCIA' => 6],
            ['NOMBRE' => 'Osa', 'IDPROVINCIA' => 6],
            ['NOMBRE' => 'Aguirre', 'IDPROVINCIA' => 6],
            ['NOMBRE' => 'Golfito', 'IDPROVINCIA' => 6],
            ['NOMBRE' => 'Coto Brus', 'IDPROVINCIA' => 6],
            ['NOMBRE' => 'Parrita', 'IDPROVINCIA' => 6],
            ['NOMBRE' => 'Corredores', 'IDPROVINCIA' => 6],
            ['NOMBRE' => 'Garabito', 'IDPROVINCIA' => 6],

            // Limón (IDPROVINCIA = 7)
            ['NOMBRE' => 'Limón', 'IDPROVINCIA' => 7],
            ['NOMBRE' => 'Pococí', 'IDPROVINCIA' => 7],
            ['NOMBRE' => 'Siquirres', 'IDPROVINCIA' => 7],
            ['NOMBRE' => 'Talamanca', 'IDPROVINCIA' => 7],
            ['NOMBRE' => 'Matina', 'IDPROVINCIA' => 7],
            ['NOMBRE' => 'Guácimo', 'IDPROVINCIA' => 7],
        ]);
    }
}
