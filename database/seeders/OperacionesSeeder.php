<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Operacion;

class OperacionesSeeder extends Seeder
{
    public function run(): void
    {
        $años = [2022, 2023, 2024, 2025];
        $operarios = ['Luis Pérez', 'Pepe Martinez', 'Carlos Gómez'];

        $tiposOperacion = [
            'poda'          => [1, 2],   // Enero-Febrero
            'abonado'       => [2, 3],   // Febrero-Marzo
            'riego'         => [5, 6, 7, 8, 9], // Mayo-Septiembre
            'herbicida'     => [3, 4, 9],        // Marzo, Abril, Septiembre
            'recoleccion'   => [10, 11, 12],     // Octubre-Diciembre
        ];

        foreach ($años as $año) {
            for ($parcela_id = 1; $parcela_id <= 13; $parcela_id++) {
                foreach ($tiposOperacion as $tipo => $meses) {
                    foreach ($meses as $mes) {
                        // No todas las parcelas tienen todas las operaciones
                        if (rand(0, 2) === 0) continue;

                        $dia = rand(1, 20);
                        $hora = rand(7, 12);

                        Operacion::create([
                            'parcela_id'       => $parcela_id,
                            'usuario_id'       => 1,
                            'operario'         => $operarios[array_rand($operarios)],
                            'tipo_operacion'   => $tipo,
                            'hora_inicio'      => "$año-$mes-$dia $hora:00:00",
                            'duracion_minutos' => rand(60, 480),
                            'precio'           => rand(40, 200),
                            'descripcion'      => ucfirst($tipo) . ' campaña ' . $año,
                            'estado'           => 'realizada',
                        ]);
                    }
                }
            }
        }
    }
}