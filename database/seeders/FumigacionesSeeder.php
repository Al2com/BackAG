<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Fumigacion;

class FumigacionesSeeder extends Seeder
{
    public function run(): void
    {
        $años = [2022, 2023, 2024, 2025];
        $operarios = ['Luis Pérez', 'Pepe Martinez'];

        // Fumigaciones realistas por temporada del kaki
        // Marzo-Abril: tratamientos preventivos inicio vegetación
        // Mayo-Junio: fungicidas + insecticidas plaga
        // Julio-Agosto: acaricidas + correctores
        // Septiembre-Octubre: previo recolección
        $meses = [3, 4, 5, 6, 7, 8, 9, 10];

        foreach ($años as $año) {
            for ($parcela_id = 1; $parcela_id <= 13; $parcela_id++) {
                foreach ($meses as $mes) {
                    // No todas las parcelas se fumigan todos los meses
                    if (rand(0, 2) === 0) continue;

                    $metodo = rand(0, 1) ? 'tractor' : 'mochila';
                    $dia = rand(1, 20);
                    $hora = rand(7, 10);

                    $fumigacion = Fumigacion::create([
                        'parcela_id'        => $parcela_id,
                        'usuario_id'        => 1,
                        'operario'          => $metodo === 'mochila' ? $operarios[array_rand($operarios)] : null,
                        'hora_inicio'       => "$año-$mes-$dia $hora:00:00",
                        'duracion_minutos'  => $metodo === 'mochila' ? rand(60, 240) : null,
                        'metodo_aplicacion' => $metodo,
                        'turbos'            => $metodo === 'tractor' ? rand(1, 4) : null,
                        'mochilas'          => $metodo === 'mochila' ? rand(1, 3) : null,
                        'precio'            => $metodo === 'tractor' ? rand(80, 150) : rand(30, 70),
                        'descripcion'       => 'Tratamiento fitosanitario campaña ' . $año,
                        'estado'            => 'realizada',
                    ]);

                    // Asociar 1 o 2 productos a cada fumigación
                    // Productos fitosanitarios: IDs 1-7 (Sercadis, Ortiva, Noble, Volquete, Omite Top, Karate, Piriproxifen)
                    $numProductos = rand(1, 2);
                    $productosIds = array_rand(array_flip(range(1, 7)), $numProductos);
                    if (!is_array($productosIds)) $productosIds = [$productosIds];

                    foreach ($productosIds as $productoId) {
                        $fumigacion->Productos()->attach($productoId, [
                            'dosis_introducida' => round(rand(3, 8) / 10, 1),
                            'cantidad'          => round(rand(3, 8) / 10, 1),
                        ]);
                    }
                }
            }
        }
    }
}