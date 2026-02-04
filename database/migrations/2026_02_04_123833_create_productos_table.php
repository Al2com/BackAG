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
    $table->decimal('precio', 6, 2); // ej: 9999.99
    $table->string('ubicacion');
    $table->unsignedInteger('stock_minimo');
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
