<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void{
    Schema::create('gastos_riego', function (Blueprint $table) {
        $table->id();
        $table->foreignId('parcela_id')->constrained('parcelas')->onDelete('cascade');
        $table->unsignedSmallInteger('anio');
        $table->unsignedTinyInteger('mes');
        $table->enum('concepto', ['agua', 'abono', 'mantenimiento']);
        $table->decimal('importe', 8, 2);
        $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();

        // un apunte por parcela, mes y concepto
        $table->unique(['parcela_id', 'anio', 'mes', 'concepto']);
    });
}

public function down(): void{
    Schema::dropIfExists('gastos_riego');
}

};
