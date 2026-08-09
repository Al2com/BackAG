<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\PerteneceAdmin;

class Fumigacion extends Model
{
    use PerteneceAdmin;

    protected $table = 'fumigaciones';
    protected $fillable = [
        'parcela_id',
        'lote_id',
        'operacion_id',
        'usuario_id',
        'operario',
        'estado',
        'metodo_aplicacion',
        'hora_inicio',
        'duracion_minutos',
        'mochilas',
        'litros_agua',
        'precio',
        'num_parcelas',
        'turbos',
        'precio_turbo',
        'descripcion',
        // 'admin_id'
    ];

    protected $casts = [
        'precio_turbo' => 'decimal:2',
    ];

    /**
     * Coste de operación (total, sin repartir): el pase completo de tractor
     * (turbos × precio_turbo) o el precio ya cerrado si es mochila.
     * Para el reparto proporcional entre parcelas de un mismo pase de
     * tractor, usar CosteFumigacionService::costeTractorParcela en su lugar:
     * este accessor es el coste de ESTA fumigación tal cual, sin prorratear.
     *
     * @var list<string>
     */
    protected $appends = [
        'coste_operacion',
        'desglose_productos',
        'total',
    ];

    public function Operaciones(){
        return $this->belongsTo(Operacion::class);
    }

    public function Productos(){
        return $this->belongsToMany(Producto::class, 'fumigacion_producto')
        ->withPivot('cantidad','dosis_introducida','precio');
    }

    public function parcela(){
        return $this->belongsTo(Parcela::class, 'parcela_id');
    }

    public function getCosteOperacionAttribute(): float
    {
        $coste = $this->metodo_aplicacion === 'tractor'
            ? (float) $this->turbos * (float) $this->precio_turbo
            : (float) $this->precio;

        return round($coste, 2);
    }

    public function getDesgloseProductosAttribute(): array
    {
        return $this->productos->map(function ($producto) {
            $cantidad = (float) $producto->pivot->cantidad;
            // el precio se congela en la pivote al fumigar; en filas antiguas
            // sin precio guardado, se cae al precio actual del producto
            $precioUnitario = (float) ($producto->pivot->precio ?? $producto->precio ?? 0);

            return [
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre,
                'unidad' => $producto->unidad,
                'cantidad' => round($cantidad, 2),
                'precio_unitario' => round($precioUnitario, 2),
                'subtotal' => round($cantidad * $precioUnitario, 2),
            ];
        })->all();
    }

    public function getTotalAttribute(): float
    {
        $totalProductos = array_sum(array_column($this->desglose_productos, 'subtotal'));

        return round($this->coste_operacion + $totalProductos, 2);
    }
}