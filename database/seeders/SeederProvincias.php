<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeederProvincias extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('provincias')->insert([
            ['NOMBRE' => 'San José'],
            ['NOMBRE' => 'Alajuela'],
            ['NOMBRE' => 'Cartago'],
            ['NOMBRE' => 'Heredia'],
            ['NOMBRE' => 'Guanacaste'],
            ['NOMBRE' => 'Puntarenas'],
            ['NOMBRE' => 'Limón'],
        ]);
    }
}
