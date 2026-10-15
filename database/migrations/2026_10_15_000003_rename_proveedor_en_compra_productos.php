<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // La columna de texto libre se llamaba 'proveedor', que choca con la
    // relación proveedor() del modelo (Eloquent prioriza el atributo y rompe
    // el acceso a la relación). Se renombra a 'proveedor_nombre'.
    public function up(): void
    {
        Schema::table('compra_productos', function (Blueprint $table) {
            $table->renameColumn('proveedor', 'proveedor_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('compra_productos', function (Blueprint $table) {
            $table->renameColumn('proveedor_nombre', 'proveedor');
        });
    }
};
