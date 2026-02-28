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
        Schema::create('fumigaciones', function (Blueprint $table) {
            $table->id();


            $table->foreignId('parcela_id')->constrained('parcelas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->unsignedInteger('duracion_minutos');
            $table->text('descripcion')->nullable();
//para que las fumigaciones sean con tractor o mochila 
            $table->enum('metodo_aplicacion', ['tractor', 'mochila'])->default('tractor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fumigaciones');
    }
};
