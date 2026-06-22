<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Producto;
class ProductoSeeder extends Seeder
{
    public function run(): void{

    $alvaro = \App\Models\User::where('email', 'alvaro@test.com')->first();

    // Productos del catálogo de la cooperativa (Canso), cítricos + caqui.
    // El nombre comercial y la materia activa van EXACTAMENTE igual que en el
    // catálogo del front (cuaderno.jsx) para que el cuaderno case los productos
    // usados sin ambigüedad. Los campos de inventario (precio, stock, ubicación,
    // dosis) son de relleno: el catálogo de la cooperativa no los trae.
    // [nombre, materia_activa, unidad]
        $catalogo = [
            // ---- Cítricos ----
            ['Albelda ce',      'Aceite de parafina 79%', 'L'],
            ['Cesar',           'Hexitiazox 10%',         'kg'],
            ['Poseidon',        'Piridaben 10%',          'L'],
            ['Dinamite',        'Acequinocil 16,4%',      'L'],
            ['Gazel',           'Acetamiprid 20%',        'kg'],
            ['Flash um',        'Fenpiroximato 5,12%',    'L'],
            ['Evure',           'Tau Fluvalinato 24%',    'L'],
            ['Carnadine',       'Acetamiprid 20%',        'L'],
            ['Atominal EC',     'Piriproxifen 10%',       'L'],
            ['Closer',          'Sulfoxaflor 12%',        'L'],
            ['Aliette wg',      'Fosetil-Al 80%',         'kg'],
            ['Oxicoop 50',      'Ox. Cl. Cobre 50%',      'kg'],
            ['Beretox 40 SG',   'Ac. Giberélico 40%',     'kg'],
            ['Citrolina',       'Aceite de parafina 79%', 'L'],
            ['Spintor Cebo',    'Spinosad 0,024%',        'L'],
            ['Shark',           'Etofenprox 28,75%',      'L'],
            ['Koromite',        'Milbemectina 0,93%',     'L'],
            ['Tiazosac',        'Hexitiazox 25,87%',      'L'],
            ['Spintor 480 SC',  'Spinosad 48%',           'L'],

            // ---- Caqui (solo los que no se repiten con cítricos) ----
            ['Dipel DF',        'Bacillus Thuringiensis K.',          'kg'],
            ['Align',           'Azadiractin 3,2%',                   'L'],
            ['Karate Zeon',     'Lambda Cihalotrin 10%',              'L'],
            ['Movento Gold',    'Spirotetramat 10%',                  'L'],
            ['Junival',         'Piriproxifen 10%',                   'L'],
            ['Ovipron Top',     'Aceite de parafina 80%',             'L'],
            ['Cabrio wg',       'Piraclostrobin 20%',                 'kg'],
            ['Ortiva',          'Azoxistrobin 25%',                   'L'],
            ['Score',           'Difenoconazol 25%',                  'L'],
            ['Merplus',         'Potasio 66% + captan 36%',           'L'],
            ['Sercadis',        'Fluxapyroxad 30%',                   'L'],
            ['Fruitel',         'Etefon 48%',                         'L'],
            ['Berelex',         'Ácido giberélico 40%',               'kg'],
            ['Mojante Norton',  'Alquil poliglicol 20%',              'L'],
            ['Omite Top',       'Propargite 30% + Cyflumetofen 10%',  'L'],

            // ---- Otros productos del almacén (NO están en el cuaderno de Canso) ----
            // Inventario realista (abonos, herbicidas de suelo, correctores,
            // fungicidas). Aunque se usen en fumigaciones, NO se marcarán al
            // generar el cuaderno porque no figuran en su catálogo impreso.
            ['Noble',                 'Folpet 50%',                    'kg'],
            ['Volquete',              'Captan 80%',                    'kg'],
            ['Corrector MN',          'Manganeso quelado 6%',          'L'],
            ['Corrector Ca',          'Calcio quelado 15%',            'L'],
            ['Aminoácidos',           'Aminoácidos libres 40%',        'L'],
            ['Abono Amoniaco',        'Nitrógeno amoniacal 20%',       'L'],
            ['Abono 15-15-15',        'NPK 15-15-15',                  'kg'],
            ['Abono Calcio',          'Calcio 18% + Boro 0.2%',        'kg'],
            ['Abono Fem',             'Ácidos húmicos y fúlvicos 12%', 'L'],
            ['Insecticida Glifosato', 'Glifosato 36%',                 'L'],
            ['Insecticida U46',       'MCPA 50%',                      'L'],
            ['Insecticida Goal',      'Oxifluorfén 24%',               'L'],
        ];

        foreach ($catalogo as [$nombre, $materiaActiva, $unidad]) {
            Producto::create([
                'nombre'         => $nombre,
                'materia_activa' => $materiaActiva,
                'precio'         => null,
                'ubicacion'      => 'Almacén',
                'stock_minimo'   => 5,
                'stock_actual'   => 0,
                'dosis_recomendada' => null,
                'unidad'         => $unidad,
                'admin_id'       => $alvaro->id,
            ]);
        }
    }
}