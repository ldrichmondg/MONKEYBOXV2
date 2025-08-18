<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prealerta', function (Blueprint $table) {
            $table->id();
            $table->string('DESCRIPCION', 250);
            $table->decimal('VALOR', 12, 3);
            $table->string('NOMBRETIENDA', 150);
            $table->tinyInteger('IDCOURIER')->default(0);
            $table->unsignedBigInteger('IDPREALERTA')->nullable(); //porque el proveedor ML no tiene API
            $table->unsignedBigInteger('IDTRACKINGPROVEEDOR');
            $table->foreign('IDTRACKINGPROVEEDOR')->references('id')->on('trackingproveedor')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prealerta');
    }
};
