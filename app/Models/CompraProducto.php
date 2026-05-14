<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraProducto extends Model
{
    protected $table = 'compra_productos';

    protected $fillable = [
        'user_id',
        'producto_id',
        'proveedor_id',
        'cantidad_compra',
        'precio',
        'fecha_compra',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function producto() {
        return $this->belongsTo(Producto::class);
    }

    public function proveedor() {
        return $this->belongsTo(Proveedor::class);
    }
}