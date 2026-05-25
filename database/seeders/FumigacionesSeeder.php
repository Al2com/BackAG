<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Fumigacion;

class FumigacionesSeeder extends Seeder
{
    public function run(): void
    {
        // productos que se pueden usar solo con mochila (herbicidas de suelo)
        $productosMochila = [
            ['id' => 16, 'dosis' => 0.2],  // Glifosato
            ['id' => 17, 'dosis' => 0.15], // U46
            ['id' => 18, 'dosis' => 0.10], // Goal
        ];

        // productos para tractor (fungicidas e insecticidas foliares)
        $productosTractor = [
            ['id' => 1, 'dosis' => 0.4],  // Sercadis
            ['id' => 2, 'dosis' => 0.5],  // Ortiva
            ['id' => 3, 'dosis' => 1.5],  // Noble
            ['id' => 4, 'dosis' => 1.2],  // Volquete
            ['id' => 5, 'dosis' => 0.6],  // Omite Top
            ['id' => 6, 'dosis' => 0.3],  // Karate
            ['id' => 7, 'dosis' => 0.5],  // Piriproxifen
            ['id' => 8, 'dosis' => 2.5],  // Fosetil A
        ];

        $operarios = ['Luis Pérez', 'Pepe Martinez'];
        $años = [2022, 2023, 2024, 2025];

        foreach ($años as $año) {
            for ($parcela_id = 1; $parcela_id <= 13; $parcela_id++) {
                // 4 fumigaciones por parcela y año, mezcla de tractor y mochila
                for ($i = 0; $i < 4; $i++) {
                    $metodo = $i < 2 ? 'tractor' : 'mochila'; // 2 de tractor, 2 de mochila
                    $mes = rand(3, 10);
                    $dia = rand(1, 20);
                    $hora = rand(7, 10);

                    $fumigacion = Fumigacion::create([
                        'parcela_id'        => $parcela_id,
                        'usuario_id'        => 1,
                        'operario'          => $metodo === 'mochila' ? $operarios[array_rand($operarios)] : null,
                        'hora_inicio'       => sprintf('%04d-%02d-%02d %02d:00:00', $año, $mes, $dia, $hora),
                        'duracion_minutos'  => $metodo === 'mochila' ? rand(60, 240) : null,
                        'metodo_aplicacion' => $metodo,
                        'turbos'            => $metodo === 'tractor' ? rand(1, 4) : null,
                        'mochilas'          => $metodo === 'mochila' ? rand(1, 3) : null,
                        'precio'            => $metodo === 'tractor' ? rand(80, 150) : rand(30, 70),
                        'descripcion'       => 'Tratamiento fitosanitario campaña ' . $año,
                        'estado'            => 'realizada',
                    ]);

                    if ($metodo === 'mochila') {
                        // mochila: solo herbicidas, cogemos 1 o 2 al azar
                        $seleccion = collect($productosMochila)->random(rand(1, 2));
                        foreach ($seleccion as $prod) {
                            $fumigacion->Productos()->attach($prod['id'], [
                                'dosis_introducida' => $prod['dosis'],
                                'cantidad'          => $prod['dosis'],
                            ]);
                        }
                    } else {
                        // tractor: fungicidas/insecticidas, cogemos 1 o 2 al azar
                        $seleccion = collect($productosTractor)->random(rand(1, 2));
                        foreach ($seleccion as $prod) {
                            $fumigacion->Productos()->attach($prod['id'], [
                                'dosis_introducida' => $prod['dosis'],
                                'cantidad'          => $prod['dosis'],
                            ]);
                        }
                    }
                }
            }
        }
    }
}