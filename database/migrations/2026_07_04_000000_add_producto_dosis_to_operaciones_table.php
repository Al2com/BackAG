<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Añade a operaciones todo lo necesario para abonado:
// - producto_id / dosis: qué producto y cuánta cantidad se consumió (antes solo
//   se guardaba el precio ya calculado, sin rastro del producto ni de la dosis).
// - precio_material: la parte de 'precio' que corresponde solo a material: el
//   resto (precio - precio_material) es mano de obra. 'precio' sigue siendo el
//   total, como ya lo usan todos los sitios que suman el gasto (GastosController).
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
            $table->dropColumn(['dosis', 'precio_material']);
        });
    }
};