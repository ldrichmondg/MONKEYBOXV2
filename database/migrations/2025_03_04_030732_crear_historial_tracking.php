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
        Schema::create('historialtracking', function (Blueprint $table) {
            $table->id();
            $table->string('DESCRIPCION', 380);
            $table->string('DESCRIPCIONMODIFICADA', 380);
            $table->integer('CODIGOPOSTAL');
            $table->string('PAISESTADO', 75);
            $table->boolean('OCULTADO');
            $table->smallInteger('TIPO');
            $table->unsignedBigInteger('IDTRACKING');
            $table->foreign('IDTRACKING')->references('id')->on('tracking')->onDelete('cascade');
            $table->date('FECHA')->nullable()->change();
            $table->softDeletes();
            $table->timestamps();
            $table->smallInteger('IDCOURIER');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historialtracking');
    }
};
