<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Añade producto_id y dosis a operaciones. Solo se rellenan cuando
// tipo_operacion = 'abonado': permiten saber qué producto y cuánta cantidad
// se consumió para poder descontarlo del almacén (antes solo se guardaba
// el precio ya calculado, sin rastro del producto ni de la dosis).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operaciones', function (Blueprint $table) {
            $table->foreignId('producto_id')->nullable()->after('tipo_operacion')
                ->constrained('productos')->nullOnDelete();
            $table->decimal('dosis', 8, 2)->nullable()->after('producto_id');
        });
    }

    public function down(): void
    {
        Schema::table('operaciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('producto_id');
            $table->dropColumn('dosis');
        });
    }
};
