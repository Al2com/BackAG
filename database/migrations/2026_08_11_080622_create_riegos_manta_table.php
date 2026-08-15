<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Riego a manta: evento por día (no mensual como gastos_riego), con importe
// calculado desde las hanegadas de la parcela. Un riego puede cubrir varias
// parcelas a la vez (selección libre): cada parcela guarda su propia fila con
// su propio importe, y las filas del mismo evento comparten lote_id (mismo
// patrón que fumigaciones.lote_id para pases de tractor a varias parcelas).
// No toca gastos_riego: esa tabla se sigue usando para goteo (agua) y para
// el mantenimiento de ambos tipos de parcela.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riegos_manta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcela_id')->constrained('parcelas')->cascadeOnDelete();
            $table->uuid('lote_id')->nullable()->index();
            $table->date('fecha');
            $table->decimal('precio_por_hanegada', 8, 2);
            // copia de dimension_hanegadas en el momento del riego: si luego se
            // edita la parcela, el histórico de este riego no cambia con ella
            $table->decimal('hanegadas', 6, 3);
            $table->decimal('importe', 8, 2);
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riegos_manta');
    }
};
