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
        Schema::create('compra_productos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
    $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();//relacion con users
    $table->dateTime('fecha_compra');
    $table->decimal('cantidad_compra', 7, 2);
    $table->decimal('precio', 7, 2);
    $table->timestamps();
    });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compra_productos');
    }
};
