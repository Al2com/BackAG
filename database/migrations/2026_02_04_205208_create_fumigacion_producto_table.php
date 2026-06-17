<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void{
        Schema::create('fumigacion_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->cascadeOnDelete();
            $table->foreignId('fumigacion_id')->nullable()->constrained('fumigaciones')->cascadeOnDelete();
            $table->decimal('cantidad', 7, 2);
            $table->decimal('dosis_introducida', 7, 2);
            // $table->decimal('precio', 8, 2)->nullable(); // coste unitario congelado al crear la fumigación
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fumigacion_producto');
    }
};
