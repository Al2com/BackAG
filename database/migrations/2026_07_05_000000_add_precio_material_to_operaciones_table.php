<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// precio_material es la parte de 'precio' que corresponde solo a material: el
// resto (precio - precio_material) es mano de obra. 'precio' sigue siendo el
// total, como ya lo usan todos los sitios que suman el gasto (GastosController).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operaciones', function (Blueprint $table) {
            $table->decimal('precio_material', 8, 2)->nullable()->after('precio');
        });
    }

    public function down(): void
    {
        Schema::table('operaciones', function (Blueprint $table) {
            $table->dropColumn('precio_material');
        });
    }
};