<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Columnas nuevas del rediseño de almacén.
        Schema::table('productos', function (Blueprint $table) {
            // categoría: texto libre descriptivo ("Fungicida · Rovral"), no un enum
            $table->string('categoria', 100)->nullable()->after('materia_activa');
            // máximo recomendado, para la barra de nivel del detalle
            $table->decimal('stock_maximo', 10, 2)->nullable()->after('stock_minimo');
        });

        // Stock a decimal: las compras y los consumos son decimales; si el
        // stock fuese entero, una compra de 10,50 kg se truncaría. No es
        // destructivo: los enteros actuales pasan a x.00.
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('stock_minimo', 10, 2)->change();
            $table->decimal('stock_actual', 10, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['categoria', 'stock_maximo']);
            $table->unsignedInteger('stock_minimo')->change();
            $table->unsignedInteger('stock_actual')->default(0)->change();
        });
    }
};
