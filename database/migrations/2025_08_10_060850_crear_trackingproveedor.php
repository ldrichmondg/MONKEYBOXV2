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
        Schema::create('trackingproveedor', function (Blueprint $table) {
            $table->id();
            $table->string('TRACKINGPROVEEDOR')->nullable();
            $table->unsignedBigInteger('IDPROVEEDOR');
            $table->unsignedBigInteger('IDTRACKING');
            $table->foreign('IDPROVEEDOR')->references('id')->on('proveedor');
            $table->foreign('IDTRACKING')->references('id')->on('tracking')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trackingproveedor');
    }
};
