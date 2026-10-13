<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parcelas', function (Blueprint $table) {
            // Datos del Catastro, cacheados tras la primera consulta. Son nullable
            // porque las parcelas ya existentes todavía no los tienen.
            $table->string('referencia_catastral', 20)->nullable()->after('parcela');

            // decimal (NO float): las coordenadas necesitan valor exacto y
            // comparaciones/almacenamiento deterministas; float introduce error
            // binario de redondeo. (10,7): 7 decimales (~1 cm) y 3 enteros,
            // suficiente para cualquier lat/lng de España.
            $table->decimal('latitud', 10, 7)->nullable()->after('referencia_catastral');
            $table->decimal('longitud', 10, 7)->nullable()->after('latitud');
        });
    }

    public function down(): void
    {
        Schema::table('parcelas', function (Blueprint $table) {
            $table->dropColumn(['referencia_catastral', 'latitud', 'longitud']);
        });
    }
};
