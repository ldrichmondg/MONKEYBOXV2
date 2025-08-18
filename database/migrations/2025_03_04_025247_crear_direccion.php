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
        Schema::create('direccion', function (Blueprint $table) {
            $table->id();
            $table->string('DIRECCION', 240);
            $table->smallInteger('TIPO');
            $table->unsignedBigInteger('IDCLIENTE');
            $table->integer('CODIGOPOSTAL');
            $table->foreign('IDCLIENTE')->references('id')->on('cliente');
            $table->string('PAISESTADO', 75);
            $table->string('LINKWAZE')->default("")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direccion');
    }
};
