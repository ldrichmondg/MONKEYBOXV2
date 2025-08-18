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
        Schema::create('estadocliente', function (Blueprint $table) {
            $table->id();
            $table->string('DESCRIPCION');
            $table->string('COLOR');
            $table->string('DESCRIPCIONESTADOMBOX', 35);
            $table->foreign('DESCRIPCIONESTADOMBOX')->references('DESCRIPCION')->on('estadombox');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estadocliente');
    }
};
