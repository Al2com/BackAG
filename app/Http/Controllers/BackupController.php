<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ZipArchive;

class BackupController extends Controller
{
    /**
     * Tablas de dominio exportables/importables, en orden de dependencia
     * (padres antes que hijos: una fila solo referencia ids de tablas que
     * ya aparecen antes en esta lista). 'fk' mapea columna => tabla de
     * dominio referenciada, para poder remapear ids al importar.
     * Las tablas internas de Laravel y 'users' quedan fuera a propósito.
     */
    private const TABLAS = [
        'propietarios' => ['fk' => []],
        'explotaciones' => ['fk' => ['propietario_id' => 'propietarios']],
        'parcelas' => ['fk' => ['explotacion_id' => 'explotaciones', 'propietarios_id' => 'propietarios']],
        'productos' => ['fk' => []],
        'proveedores' => ['fk' => []],
        'operaciones' => ['fk' => ['parcela_id' => 'parcelas', 'producto_id' => 'productos']],
        'fumigaciones' => ['fk' => ['parcela_id' => 'parcelas', 'operacion_id' => 'operaciones']],
        'fumigacion_producto' => ['fk' => ['producto_id' => 'productos', 'fumigacion_id' => 'fumigaciones']],
        'compra_productos' => ['fk' => ['producto_id' => 'productos', 'proveedor_id' => 'proveedores']],
        'recoleccion' => ['fk' => ['parcela_id' => 'parcelas']],
        'gastos_riego' => ['fk' => ['parcela_id' => 'parcelas']],
    ];

    // Columnas que referencian 'users' y no se pueden remapear al importar
    // (los ids de usuario del backup no tienen por qué existir en destino).
    // admin_id se fuerza siempre al inquilino que importa; el resto se anula.
    private const COLUMNAS_USUARIO_ANULABLES = ['usuario_id', 'user_id'];

    public function tieneDatos(Request $request)
    {
        $adminId = $request->user()->adminId();

        foreach (self::TABLAS as $tabla => $info) {
            if (! Schema::hasColumn($tabla, 'admin_id')) {
                continue;
            }
            if (DB::table($tabla)->where('admin_id', $adminId)->exists()) {
                return response()->json(['tiene_datos' => true]);
            }
        }

        return response()->json(['tiene_datos' => false]);
    }

    /**
     * Filas de $tabla que pertenecen al inquilino $adminId.
     * Si la tabla no tiene admin_id propio (pivote), se apoya en la primera
     * FK declarada hacia una tabla de dominio ya procesada (que sí lo tiene).
     */
    private function filasDelTenant(string $tabla, int $adminId, array $idsExportadosPorTabla): \Illuminate\Support\Collection
    {
        if (Schema::hasColumn($tabla, 'admin_id')) {
            return DB::table($tabla)->where('admin_id', $adminId)->get();
        }

        foreach (self::TABLAS[$tabla]['fk'] as $columna => $tablaPadre) {
            if (isset($idsExportadosPorTabla[$tablaPadre])) {
                return DB::table($tabla)->whereIn($columna, $idsExportadosPorTabla[$tablaPadre])->get();
            }
        }

        return collect();
    }

    /**
     * Descarga en ZIP con un CSV por tabla, para consulta fuera de la app.
     */
    public function exportarCsv(Request $request)
    {
        $adminId = $request->user()->adminId();
        $idsExportados = [];

        $rutaTmpZip = tempnam(sys_get_temp_dir(), 'backup_csv_');
        $zip = new ZipArchive();
        $zip->open($rutaTmpZip, ZipArchive::OVERWRITE);

        foreach (self::TABLAS as $tabla => $info) {
            $filas = $this->filasDelTenant($tabla, $adminId, $idsExportados);
            $idsExportados[$tabla] = $filas->pluck('id')->all();

            $csv = fopen('php://temp', 'r+');
            if ($filas->isNotEmpty()) {
                fputcsv($csv, array_keys((array) $filas->first()));
                foreach ($filas as $fila) {
                    fputcsv($csv, (array) $fila);
                }
            } else {
                $columnas = Schema::getColumnListing($tabla);
                fputcsv($csv, $columnas);
            }
            rewind($csv);
            $zip->addFromString("{$tabla}.csv", stream_get_contents($csv));
            fclose($csv);
        }

        $zip->close();

        $nombreArchivo = 'agrogestion-datos-' . now()->format('Y-m-d') . '.zip';

        return response()->download($rutaTmpZip, $nombreArchivo)->deleteFileAfterSend(true);
    }

    /**
     * Descarga en JSON con todos los datos del inquilino + metadatos, para
     * poder restaurarlos después con /backup/importar.
     */
    public function exportarJson(Request $request)
    {
        $adminId = $request->user()->adminId();
        $idsExportados = [];
        $tablas = [];

        foreach (self::TABLAS as $tabla => $info) {
            $filas = $this->filasDelTenant($tabla, $adminId, $idsExportados);
            $idsExportados[$tabla] = $filas->pluck('id')->all();
            $tablas[$tabla] = $filas->values();
        }

        $backup = [
            'meta' => [
                'fecha_exportacion' => now()->toIso8601String(),
                'version_app' => config('backup.version_app'),
                'version_esquema' => config('backup.version_esquema'),
            ],
            'tablas' => $tablas,
        ];

        $nombreArchivo = 'agrogestion-backup-' . now()->format('Y-m-d') . '.json';

        return response()->streamDownload(
            fn () => print(json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            $nombreArchivo,
            ['Content-Type' => 'application/json']
        );
    }

    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:json', 'max:20480'],
        ]);

        $contenido = json_decode(file_get_contents($request->file('archivo')->getRealPath()), true);

        if (! is_array($contenido) || ! isset($contenido['meta'], $contenido['tablas'])) {
            return response()->json(['mensaje' => 'El archivo no tiene el formato de un respaldo de AgroGestión'], 422);
        }

        if ((int) ($contenido['meta']['version_esquema'] ?? -1) !== config('backup.version_esquema')) {
            return response()->json([
                'mensaje' => 'La versión del respaldo no es compatible con esta versión de la aplicación',
            ], 422);
        }

        $usuario = $request->user();
        $adminId = $usuario->adminId();

        try {
            DB::transaction(function () use ($contenido, $adminId) {
                // 1. borra los datos actuales del inquilino, en orden inverso
                //    para no violar las FK (hijos antes que padres)
                $tablasInverso = array_reverse(array_keys(self::TABLAS));
                $idsAdminPorTabla = [];

                foreach (array_keys(self::TABLAS) as $tabla) {
                    if (Schema::hasColumn($tabla, 'admin_id')) {
                        $idsAdminPorTabla[$tabla] = DB::table($tabla)->where('admin_id', $adminId)->pluck('id')->all();
                    }
                }

                foreach ($tablasInverso as $tabla) {
                    if (Schema::hasColumn($tabla, 'admin_id')) {
                        DB::table($tabla)->where('admin_id', $adminId)->delete();
                        continue;
                    }
                    foreach (self::TABLAS[$tabla]['fk'] as $columna => $tablaPadre) {
                        if (! empty($idsAdminPorTabla[$tablaPadre])) {
                            DB::table($tabla)->whereIn($columna, $idsAdminPorTabla[$tablaPadre])->delete();
                            break;
                        }
                    }
                }

                // 2. inserta las filas del backup, remapeando ids antiguos -> nuevos
                $mapaIds = [];

                foreach (self::TABLAS as $tabla => $info) {
                    $mapaIds[$tabla] = [];
                    $filas = $contenido['tablas'][$tabla] ?? [];

                    foreach ($filas as $fila) {
                        $fila = (array) $fila;
                        $idAntiguo = $fila['id'] ?? null;
                        unset($fila['id']);

                        foreach ($info['fk'] as $columna => $tablaPadre) {
                            if (array_key_exists($columna, $fila) && $fila[$columna] !== null) {
                                $fila[$columna] = $mapaIds[$tablaPadre][$fila[$columna]] ?? null;
                            }
                        }

                        foreach (self::COLUMNAS_USUARIO_ANULABLES as $columna) {
                            if (array_key_exists($columna, $fila)) {
                                $fila[$columna] = null;
                            }
                        }

                        if (array_key_exists('admin_id', $fila)) {
                            $fila['admin_id'] = $adminId;
                        }

                        $nuevoId = DB::table($tabla)->insertGetId($fila);

                        if ($idAntiguo !== null) {
                            $mapaIds[$tabla][$idAntiguo] = $nuevoId;
                        }
                    }
                }
            });
        } catch (\Throwable $e) {
            return response()->json([
                'mensaje' => 'No se pudo importar el respaldo, no se ha modificado ningún dato',
                'error' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['mensaje' => 'Respaldo importado correctamente']);
    }
}
