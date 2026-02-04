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
        Schema::create('parcelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('explotacion_id')->constrained('explotaciones')->onDelete('set null');
            $table->foreignId('propietarios_id')->constrained('propietarios')->onDelete('set null');
            $table->enum('rol', ['manta', 'goteo'])->default('manta');
            $table->string('pol_parcela');
            $table->string('variedad');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcelas');
    }
};
