<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recoleccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcela_id')->constrained('parcelas')->onDelete('cascade');
            $table->date('fecha');                       // día de cogida
            $table->enum('tipo', ['adelanto', 'normal', 'atraso'])->default('normal');
            $table->decimal('kilos', 10, 2);             // kilos de esa tanda
            $table->decimal('precio_medio_kg', 8, 2);    // precio medio por kilo
            $table->string('variedad')->nullable();      // copia de la fruta en el momento
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recoleccion');
    }
};