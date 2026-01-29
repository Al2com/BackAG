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
        Schema::create('explotaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ubicacion');
            $table->text('descripcion');
            $table->foreignId('user_id')->constrained()->onDelate('set null'); //clave ajena con usuario
            $table->foreignId('propietario_id')->constrained('propietarios')->onDelate('set null');//clave ajena con propietario
            $table->timestamps();




        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('explotaciones');
    }
};
