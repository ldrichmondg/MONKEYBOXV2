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
        Schema::create('tracking', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('IDAPI');
            $table->string('IDTRACKING', 60)->unique();
            $table->string('DESCRIPCION', 250)->nullable();
            $table->string('DESDE', 50);
            $table->string('HASTA', 50);
            $table->string('DESTINO', 50);
            $table->string('COURIER', 50);
            $table->smallInteger('DIASTRANSITO');
            $table->decimal('PESO', 8, 3);
            $table->unsignedBigInteger('IDDIRECCION');
            $table->unsignedBigInteger('IDUSUARIO');
            $table->foreign('IDDIRECCION')->references('id')->on('direccion');
            $table->foreign('IDUSUARIO')->references('id')->on('users');
            $table->date('FECHAENTREGA')->nullable();
            $table->string('RUTAFACTURA')->nullable();
            $table->string('OBSERVACIONES', 500)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking');
    }
};
