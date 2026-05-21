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
        $tipos = ['poda', 'riego', 'abonado', 'mantenimiento', 'tractor'];

        foreach ($años as $año) {
            for ($parcela_id = 1; $parcela_id <= 13; $parcela_id++) {
                for ($i = 0; $i < 20; $i++) {
                    $mes = rand(1, 12);
                    $dia = rand(1, 20);
                    $hora = rand(7, 12);

                    Operacion::create([
                        'parcela_id'       => $parcela_id,
                        'usuario_id'       => 1,
                        'operario'         => $operarios[array_rand($operarios)],
                        'tipo_operacion'   => $tipos[array_rand($tipos)],
                        'hora_inicio'      => sprintf('%04d-%02d-%02d %02d:00:00', $año, $mes, $dia, $hora),
                        'duracion_minutos' => rand(60, 480),
                        'precio'           => rand(40, 200),
                        'descripcion'      => 'Operación campaña ' . $año,
                        'estado'           => 'realizada',
                    ]);
                }
            }
        }
    }
}