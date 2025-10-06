<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cantones', function (Blueprint $table) {
            $table->id();
            $table->string('NOMBRE', 100);
            $table->foreignId('IDPROVINCIA')->constrained('provincias')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cantones');
    }
};
