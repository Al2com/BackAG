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
        Schema::create('operaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parcela_id')->nullable()->constrained('parcelas')->nullOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipo_operacion', ['riego', 'poda', 'abonado', 'mantenimiento','fumigar']);
            $table->date('fecha');
            $table->dateTime('hora_inicio');
            $table->unsignedInteger('duracion_minutos');
            $table->text('descripcion');
            $table->text('producto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operaciones');
    }
};
