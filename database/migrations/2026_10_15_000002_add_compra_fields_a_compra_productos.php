<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compra_productos', function (Blueprint $table) {
            // proveedor como texto libre (el módulo nuevo no usa el FK proveedor_id);
            // se conserva proveedor_id para no romper el módulo de compras antiguo.
            $table->string('proveedor', 150)->nullable()->after('proveedor_id');
            $table->string('albaran', 50)->nullable()->after('precio');
            $table->text('notas')->nullable()->after('albaran');
            // soft deletes para no perder histórico contable
            $table->softDeletes();
            $table->index(['admin_id', 'producto_id', 'fecha_compra'], 'idx_compras_admin_prod_fecha');
        });

        // Ensanchar cantidad/precio y permitir precio nulo (precio_unitario opcional).
        Schema::table('compra_productos', function (Blueprint $table) {
            $table->decimal('cantidad_compra', 8, 2)->change();
            $table->decimal('precio', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('compra_productos', function (Blueprint $table) {
            $table->dropIndex('idx_compras_admin_prod_fecha');
            $table->dropSoftDeletes();
            $table->dropColumn(['proveedor', 'albaran', 'notas']);
            $table->decimal('cantidad_compra', 7, 2)->change();
            $table->decimal('precio', 7, 2)->change();
        });
    }
};
