<?php

use App\Models\Explotacion;
use App\Models\Operacion;
use App\Models\Parcela;
use App\Models\Producto;
use App\Models\Propietario;
use App\Models\Recoleccion;
use App\Models\User;
use App\Services\ConsultorContextoService;
use App\Services\CosteFumigacionService;
use App\Services\RiegoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

// Verifica que el contexto generado para el administrador A no contiene
// ningún nombre, cifra identificable ni registro del administrador B, y viceversa.
// Este test protege el requisito de aislamiento de datos entre inquilinos.
it('no filtra datos de otro administrador en el contexto', function () {
    $adminA = User::factory()->create(['rol' => 'admin', 'name' => 'AdminALFA']);
    $adminB = User::factory()->create(['rol' => 'admin', 'name' => 'AdminBETA']);

    // Los modelos rellenan admin_id desde auth()->user() en el hook "creating",
    // por eso creamos los datos de cada admin mientras ese admin está logueado.

    // ---- Datos del admin A ----
    Auth::login($adminA);

    $propA = Propietario::create([
        'nombre'   => 'PropietarioALFA',
        'dni'      => '11111111A',
        'telefono' => '600000001',
    ]);
    $explotA = Explotacion::create([
        'nombre'         => 'ExplotacionALFA',
        'ubicacion'      => 'Alzira',
        'descripcion'    => 'explotación de prueba A',
        'propietario_id' => $propA->id,
    ]);
    $parcelaA = Parcela::create([
        'nombre'              => 'ParcelaALFA',
        'poligono'            => 10,
        'parcela'             => 1,
        'variedad'            => 'Rojo brillante',
        'dimension_hanegadas' => 8,
        'num_arboles'         => 200,
        'fecha_plantacion'    => now()->subYears(5)->toDateTimeString(),
        'descripcion'         => 'parcela prueba A',
        'explotacion_id'      => $explotA->id,
        'propietarios_id'     => $propA->id,
    ]);
    Producto::create([
        'nombre'         => 'ProductoALFA',
        'materia_activa' => 'CipermetrinaALFA',
        'precio'         => 5.00,
        'ubicacion'      => 'Estante A',
        'stock_actual'   => 100,
        'stock_minimo'   => 10,
        'unidad'         => 'L',
    ]);
    Recoleccion::create([
        'parcela_id'      => $parcelaA->id,
        'fecha'           => now()->subMonths(2)->toDateString(),
        'tipo'            => 'normal',
        'kilos'           => 5000,
        'precio_medio_kg' => 0.45,
        'variedad'        => 'Rojo brillante',
    ]);
    Operacion::create([
        'parcela_id'       => $parcelaA->id,
        'operario'         => 'operarioALFA',
        'tipo_operacion'   => 'poda',
        'precio'           => 350.00,
        'duracion_minutos' => 120,
        'descripcion'      => 'poda prueba A',
        'hora_inicio'      => now()->subMonths(1)->toDateTimeString(),
    ]);

    Auth::logout();

    // ---- Datos del admin B ----
    Auth::login($adminB);

    $propB = Propietario::create([
        'nombre'   => 'PropietarioBETA',
        'dni'      => '22222222B',
        'telefono' => '600000002',
    ]);
    $explotB = Explotacion::create([
        'nombre'         => 'ExplotacionBETA',
        'ubicacion'      => 'Sueca',
        'descripcion'    => 'explotación de prueba B',
        'propietario_id' => $propB->id,
    ]);
    $parcelaB = Parcela::create([
        'nombre'              => 'ParcelaBETA',
        'poligono'            => 99,
        'parcela'             => 9,
        'variedad'            => 'Valencia Late',
        'dimension_hanegadas' => 12,
        'num_arboles'         => 300,
        'fecha_plantacion'    => now()->subYears(3)->toDateTimeString(),
        'descripcion'         => 'parcela prueba B',
        'explotacion_id'      => $explotB->id,
        'propietarios_id'     => $propB->id,
    ]);
    Producto::create([
        'nombre'         => 'ProductoBETA',
        'materia_activa' => 'GlifosatosBETA',
        'precio'         => 8.00,
        'ubicacion'      => 'Estante B',
        'stock_actual'   => 50,
        'stock_minimo'   => 5,
        'unidad'         => 'kg',
    ]);
    Recoleccion::create([
        'parcela_id'      => $parcelaB->id,
        'fecha'           => now()->subMonths(2)->toDateString(),
        'tipo'            => 'normal',
        'kilos'           => 9999,
        'precio_medio_kg' => 0.30,
        'variedad'        => 'Valencia Late',
    ]);
    Operacion::create([
        'parcela_id'       => $parcelaB->id,
        'operario'         => 'operarioBETA',
        'tipo_operacion'   => 'mantenimiento',
        'precio'           => 888.88,
        'duracion_minutos' => 60,
        'descripcion'      => 'mantenimiento prueba B',
        'hora_inicio'      => now()->subMonths(1)->toDateTimeString(),
    ]);

    Auth::logout();

    $servicio = new ConsultorContextoService(
        new CosteFumigacionService(),
        new RiegoService()
    );

    // ---- Contexto visto por el admin A ----
    Auth::login($adminA);
    $contextoA = $servicio->construir('datos generales');

    expect($contextoA)
        ->toContain('ParcelaALFA')
        ->toContain('ProductoALFA')
        ->toContain('ExplotacionALFA')
        ->not->toContain('ParcelaBETA')
        ->not->toContain('ProductoBETA')
        ->not->toContain('ExplotacionBETA')
        ->not->toContain('9.999')   // kg del admin B en formato europeo con punto de miles
        ->not->toContain('888,88'); // gasto del admin B

    Auth::logout();

    // ---- Contexto visto por el admin B ----
    Auth::login($adminB);
    $contextoB = $servicio->construir('datos generales');

    expect($contextoB)
        ->toContain('ParcelaBETA')
        ->toContain('ProductoBETA')
        ->toContain('ExplotacionBETA')
        ->not->toContain('ParcelaALFA')
        ->not->toContain('ProductoALFA')
        ->not->toContain('ExplotacionALFA')
        ->not->toContain('5.000')   // kg del admin A
        ->not->toContain('350,00'); // gasto del admin A

    Auth::logout();
});
