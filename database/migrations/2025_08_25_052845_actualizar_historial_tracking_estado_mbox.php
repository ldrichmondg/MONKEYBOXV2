<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('historialtracking', function (Blueprint $table) {

            $table->string('PERTENECEESTADO', 35)->nullable();
            $table->foreign('PERTENECEESTADO')->references('DESCRIPCION')->on('estadombox');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historialtracking', function (Blueprint $table) {
            // 1. Drop foreign key
            $table->dropForeign(['PERTENECEESTADO']);
            // 2. Drop column
            $table->dropColumn('PERTENECEESTADO');
        });
    }
};
