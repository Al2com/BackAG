<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\PerteneceAdmin;

class CompraProducto extends Model
{
    use PerteneceAdmin, SoftDeletes;

    protected $table = 'compra_productos';

    protected $fillable = [
        'user_id',
        'producto_id',
        'proveedor_id',
        'cantidad_compra',
        'precio',
        'fecha_compra',
        // campos del rediseño de almacén (proveedor en texto libre;
        // 'proveedor_nombre' para no chocar con la relación proveedor())
        'proveedor_nombre',
        'albaran',
        'notas',
        // 'admin_id'
    ];

    // sin este cast, fecha_compra llega como string plano y no se puede
    // formatear con ->format() en el resumen de compras
    protected $casts = [
        'fecha_compra'    => 'date',
        'cantidad_compra' => 'decimal:2',
        'precio'          => 'decimal:2',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function admin() {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function producto() {
        return $this->belongsTo(Producto::class);
    }

    public function proveedor() {
        return $this->belongsTo(Proveedor::class);
    }
}