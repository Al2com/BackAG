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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('materia_activa');
            $table->decimal('precio', 6, 2);
            $table->string('ubicacion');
            $table->decimal('dosis_recomendada', 7, 2);
            $table->unsignedInteger('stock_minimo');
            $table->unsignedInteger('stock_actual')->default(0);
            $table->string('unidad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
