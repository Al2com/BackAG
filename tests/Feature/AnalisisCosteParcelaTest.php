<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

// Monta un inquilino con UNA parcela que tiene de todo: operaciones, riego
// (gasto_riego + riego a manta) e impuestos. Devuelve [parcelaId, costeEsperado].
function sembrarParcelaConCostes(User $admin, int $anio): array
{
    $propId = DB::table('propietarios')->insertGetId([
        'nombre' => 'P', 'dni' => '1', 'telefono' => '1',
        'admin_id' => $admin->id, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $exploId = DB::table('explotaciones')->insertGetId([
        'nombre' => 'E', 'ubicacion' => 'U', 'descripcion' => 'D',
        'admin_id' => $admin->id, 'propietario_id' => $propId,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $parcelaId = DB::table('parcelas')->insertGetId([
        'explotacion_id' => $exploId, 'propietarios_id' => $propId, 'rol' => 'goteo',
        'poligono' => 1, 'parcela' => 1, 'nombre' => 'Pa', 'variedad' => 'navel',
        'dimension_hanegadas' => 10, 'num_arboles' => 100, 'fecha_plantacion' => now(),
        'descripcion' => 'x',
        'impuesto_municipal' => 10, 'impuesto_cequiaje' => 5, // impuestos = 15
        'admin_id' => $admin->id, 'created_at' => now(), 'updated_at' => now(),
    ]);

    // operaciones del año: 100 + 50 = 150
    foreach ([100, 50] as $precio) {
        DB::table('operaciones')->insert([
            'parcela_id' => $parcelaId, 'admin_id' => $admin->id,
            'operario' => 'O', 'tipo_operacion' => 'poda',
            'hora_inicio' => "$anio-06-15 08:00:00", 'duracion_minutos' => 60,
            'precio' => $precio, 'descripcion' => 'x',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    // gasto_riego del año: 30 (agua) + 20 (abono) = 50
    DB::table('gastos_riego')->insert([
        ['parcela_id' => $parcelaId, 'anio' => $anio, 'mes' => 3, 'concepto' => 'agua',
         'importe' => 30, 'admin_id' => $admin->id, 'created_at' => now(), 'updated_at' => now()],
        ['parcela_id' => $parcelaId, 'anio' => $anio, 'mes' => 4, 'concepto' => 'abono',
         'importe' => 20, 'admin_id' => $admin->id, 'created_at' => now(), 'updated_at' => now()],
    ]);

    // riego a manta del año: 20
    DB::table('riegos_manta')->insert([
        'parcela_id' => $parcelaId, 'fecha' => "$anio-05-10",
        'precio_por_hanegada' => 2, 'hanegadas' => 10, 'importe' => 20,
        'admin_id' => $admin->id, 'created_at' => now(), 'updated_at' => now(),
    ]);

    // operaciones 150 + riego 50 + manta 20 + impuestos 15 = 235
    return [$parcelaId, 235.0];
}

test('el coste total de la tarjeta por parcela incluye riego e impuestos, igual que la rentabilidad', function () {
    $admin = User::factory()->create(['rol' => 'admin']);
    $anio = 2026;
    [$parcelaId, $costeEsperado] = sembrarParcelaConCostes($admin, $anio);

    // tarjeta €/hanegada + semáforo (resumenParcela)
    $resumen = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/analisis/resumen?parcela_id=$parcelaId&anio=$anio&tipo=todas")
        ->assertOk()
        ->json();

    // mapa + gráfico/tabla (rentabilidad)
    $rentabilidad = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/analisis/rentabilidad?parcela_id=$parcelaId&anio=$anio")
        ->assertOk()
        ->json();

    $costeRentabilidad = (float) $rentabilidad['parcelas'][0]['costes'];

    // 1) ambos valen lo mismo (antes del fix la tarjeta se quedaba en 150)
    expect((float) $resumen['gastoTotal'])->toEqual($costeEsperado)
        ->and($costeRentabilidad)->toEqual($costeEsperado)
        ->and((float) $resumen['gastoTotal'])->toEqual($costeRentabilidad);

    // 2) el €/hanegada de la tarjeta sale del mismo coste total
    expect((float) $resumen['gastoPorHanegada'])->toEqual(round($costeEsperado / 10, 2));

    // 3) el semáforo (rentabilidad.margen) usa ese mismo coste total
    //    margen = ingresos (0 aquí) - coste total
    expect((float) $resumen['rentabilidad']['margen'])->toEqual(round(0 - $costeEsperado, 2));
});
