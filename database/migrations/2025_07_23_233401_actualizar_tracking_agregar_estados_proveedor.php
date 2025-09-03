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
        Schema::table('tracking', function (Blueprint $table) {
            $table->string('ESTADOMBOX', 35);
            $table->foreign('ESTADOMBOX')->references('DESCRIPCION')->on('estadombox');
            $table->string('ESTADOSINCRONIZADO', 35);
            $table->foreign('ESTADOSINCRONIZADO')->references('DESCRIPCION')->on('estadombox');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking', function (Blueprint $table) {
            // 1. Drop foreign key
            $table->dropForeign(['ESTADOMBOX']);
            // o explícito: $table->dropForeign('tracking_ESTADOMBOX_foreign');

            // 2. Drop column
            $table->dropColumn('ESTADOMBOX');
        });
    }
};
