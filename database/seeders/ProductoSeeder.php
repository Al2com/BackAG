<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\User;

// FUENTE ÚNICA del catálogo de productos. Siembra el MISMO catálogo para CADA
// admin del sistema, cada fila con su propio admin_id (aislamiento intacto).
//
// Reglas (de la auditoría):
//  - admin_id NO es fillable: se asigna como atributo, nunca por mass-assignment.
//  - En seeders no hay Auth: todo admin_id explícito.
//  - Este es el único seeder de productos activo. ProductoCooperativaSeeder y
//    ProductoCatalogoCanso2025Seeder quedan FUERA del flujo (no se llaman desde
//    DatabaseSeeder) para no duplicar inventario.
class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        // [nombre, materia_activa, ubicacion, stock(cantidad), unidad, precio]
        $catalogo = [
            ['Albelda ce',            'Aceite de parafina 79%',             'Almacén',       50, 'L',   2.59],
            ['Cesar',                 'Hexitiazox 10%',                     'Almacén',       0,  'kg',  null],
            ['Poseidon',              'Piridaben 10%',                      'Almacén',       0,  'L',   null],
            ['Dinamite',              'Acequinocil 16,4%',                  'Almacén',       0,  'L',   null],
            ['Gazel',                 'Acetamiprid 20%',                    'Almacén',       0,  'kg',  null],
            ['Flash um',              'Fenpiroximato 5,12%',                'Almacén',       0,  'L',   null],
            ['Evure',                 'Tau Fluvalinato 24%',                'Almacén',       0,  'L',   null],
            ['Carnadine',             'Acetamiprid 20%',                    'Almacén',       0,  'L',   null],
            ['Atominal EC',           'Piriproxifen 10%',                   'Almacén',       4,  'l',   50.00],
            ['Closer',                'Sulfoxaflor 12%',                    'Almacén',       0,  'l',   183.95],
            ['Aliette wg',            'Fosetil-Al 80%',                     'Almacén',       0,  'kg',  null],
            ['Oxicoop 50',            'Ox. Cl. Cobre 50%',                  'Almacén',       0,  'kg',  null],
            ['Berelex 40 SG',         'Ac. Giberélico 40%',                 'Almacén',       0,  'kg',  null],
            ['Citrolina',             'Aceite de parafina 79%',             'Almacén',       0,  'L',   null],
            ['Spintor Cebo',          'Spinosad 0,024%',                    'Almacén',       0,  'L',   null],
            ['Shark',                 'Etofenprox 28,75%',                  'Almacén',       0,  'L',   null],
            ['Koromite',              'Milbemectina 0,93%',                 'Almacén',       0,  'L',   null],
            ['Tiazosac',              'Hexitiazox 25,87%',                  'Almacén',       0,  'L',   null],
            ['Spintor 480 SC',        'Spinosad 48%',                       'Almacén',       0,  'L',   null],
            ['Dipel DF',              'Bacillus Thuringiensis K.',          'Almacén',       0,  'kg',  null],
            ['Align',                 'Azadiractin 3,2%',                   'Almacén',       0,  'L',   null],
            ['Karate Zeon',           'Lambda Cihalotrin 10%',              'Almacén',       2,  'l',   55.80],
            ['Movento Gold',          'Spirotetramat 10%',                  'Almacén',       0,  'L',   null],
            ['Junival',               'Piriproxifen 10%',                   'Almacén',       0,  'L',   null],
            ['Ovipron Top',           'Aceite de parafina 80%',             'Almacén',       50, 'l',   null],
            ['Cabrio wg',             'Piraclostrobin 20%',                 'Almacén',       0,  'kg',  null],
            ['Ortiva',                'Azoxistrobin 25%',                   'Almacén',       0,  'L',   null],
            ['Score',                 'Difenoconazol 25%',                  'Almacén',       1,  'l',   34.10],
            ['Merplus',               'Potasio 66% + captan 36%',           'Almacén',       0,  'L',   null],
            ['Sercadis',              'Fluxapyroxad 30%',                   'Almacén',       0,  'l',   136.40],
            ['Fruitel',               'Etefon 48%',                         'Almacén',       0,  'L',   null],
            ['Berelex',               'Ácido giberélico 40%',               'Almacén',       0,  'kg',  null],
            ['Mojante Norton',        'Alquil poliglicol 20%',              'Almacén',       0,  'L',   null],
            ['Omite Top',             'Propargite 30% + Cyflumetofen 10%',  'Almacén',       0,  'L',   null],
            ['Noble',                 'Folpet 50%',                         'Almacén',       0,  'kg',  null],
            ['Volquete',              'Captan 80%',                         'Almacén',       0,  'l',   252.50],
            ['Corrector MN',          'Manganeso quelado 6%',               'Almacén',       10, 'l',   4.18],
            ['Corrector Ca',          'Calcio quelado 15%',                 'Almacén',       0,  'l',   3.96],
            ['Aminoácidos',           'Aminoácidos libres 40%',             'Almacén',       0,  'L',   null],
            ['Abono Amoniaco',        'Nitrógeno amoniacal 20%',            'Almacén',       0,  'L',   null],
            ['Abono 15-15-15',        'NPK 15-15-15',                       'Almacén',       0,  'kg',  0.51],
            ['Abono Calcio',          'Calcio 18% + Boro 0.2%',             'Almacén',       10, 'l',   3.96],
            ['Labin',                 'Ácidos húmicos y fúlvicos 24%',      'Almacén',       8,  'l',   4.50],
            ['Insecticida Glifosato', 'Glifosato 36%',                      'Almacén',       20,  'l',   3.85],
            ['Insecticida U46',       'MCPA 50%',                           'Almacén',       10,  'l',   4.00],
            ['Insecticida Goal',      'Oxifluorfén 24%',                    'Almacén',       10,  'l',   25.85],
            ['Movento o-teq',         'Spirotetramat 15%',                  'Estanteria A9', 0,  null,  125.00],
        ];

        $admins = User::where('rol', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->command->warn('No hay admins (¿ejecutaste UserSeeder antes?). No se siembran productos.');
            return;
        }

        foreach ($admins as $admin) {
            foreach ($catalogo as [$nombre, $materiaActiva, $ubicacion, $stock, $unidad, $precio]) {
                // admin_id como ATRIBUTO: no es fillable (Fase 1) y en seeder no
                // hay usuario logueado que lo rellene vía el trait.
                $producto = new Producto();
                $producto->nombre            = $nombre;
                $producto->materia_activa    = $materiaActiva;
                $producto->ubicacion         = $ubicacion;
                $producto->stock_minimo      = 5;
                $producto->stock_actual      = $stock;
                $producto->dosis_recomendada = null;
                $producto->unidad            = $unidad;
                $producto->precio            = $precio;
                $producto->admin_id          = $admin->id;
                $producto->save();
            }
        }

        $this->command->info(
            'Catálogo sembrado: ' . count($catalogo) . ' productos para ' . $admins->count() . ' admin(s).'
        );
    }
}