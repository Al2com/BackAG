<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('tema', ['claro', 'oscuro'])->default('claro')->after('rol');
            $table->string('foto_perfil')->nullable()->after('tema');
            $table->string('foto_perfil_thumb')->nullable()->after('foto_perfil');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tema', 'foto_perfil', 'foto_perfil_thumb']);
        });
    }
};
