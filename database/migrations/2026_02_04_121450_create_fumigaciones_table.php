<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fumigaciones', function (Blueprint $table) {

            $table->id();

            // FK obligatoria — una fumigación siempre pertenece a una parcela
            // Si se elimina la parcela, se eliminan también todas sus fumigaciones
            $table->foreignId('parcela_id')->constrained('parcelas')->cascadeOnDelete();
            // FK opcional — herencia: una fumigación puede ser el desarrollo específico de una operación
            // Si se elimina la operación, este campo queda a null pero la fumigación se conserva
            $table->foreignId('operacion_id')->nullable()->constrained('operaciones')->nullOnDelete();
            // FK opcional — usuario del sistema que registró la fumigación
            // Preparada para escalar la aplicación con gestión multiusuario
            $table->foreignId('usuario_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('operario')->nullable();
            $table->dateTime('hora_inicio');
            $table->unsignedInteger('duracion_minutos')->nullable();
            $table->text('descripcion')->nullable();
            $table->enum('metodo_aplicacion', ['tractor', 'mochila'])->default('tractor');
            $table->string('turbos')->nullable();
            $table->string('mochilas')->nullable();
            $table->enum('estado', ['pendiente', 'realizada', 'revisada'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fumigaciones');
    }
};