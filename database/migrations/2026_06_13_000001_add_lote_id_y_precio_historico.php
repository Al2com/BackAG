<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Esta migracion añade dos campos para corregir dos problemas de Gastos:
//
// 1) lote_id en fumigaciones: cuando se fumiga con tractor varias parcelas a
//    la vez, el formulario crea una fila por parcela. Hasta ahora el "lote"
//    se reconstruia adivinando (misma hora_inicio + metodo + turbos), lo que
//    podia mezclar fumigaciones que no tenian nada que ver si coincidian en
//    esos tres valores. Con lote_id las filas creadas juntas comparten un
//    UUID generado al crearlas y el agrupamiento es exacto.
//
// 2) precio en fumigacion_producto (pivote): el coste del material en Gastos
//    se calculaba con el precio ACTUAL del producto, asi que una compra a
//    otro precio reescribia los gastos de campañas pasadas. Ahora el precio
//    vigente se congela en la pivote al registrar la fumigacion y el
//    historico no cambia aunque el producto suba o baje.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fumigaciones', function (Blueprint $table) {
            // nullable porque las fumigaciones antiguas no tienen lote;
            // index porque se agrupa por este campo en los listados
            $table->uuid('lote_id')->nullable()->index()->after('parcela_id');
        });

        Schema::table('fumigacion_producto', function (Blueprint $table) {
            // precio unitario del producto en el momento de fumigar
            // nullable: en filas antiguas no existe y el front usa el actual
            $table->decimal('precio', 8, 2)->nullable()->after('dosis_introducida');
        });
    }

    public function down(): void
    {
        Schema::table('fumigaciones', function (Blueprint $table) {
            $table->dropColumn('lote_id');
        });

        Schema::table('fumigacion_producto', function (Blueprint $table) {
            $table->dropColumn('precio');
        });
    }
};
