<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\PerteneceAdmin;

class CompraProducto extends Model
{
    use PerteneceAdmin;

    protected $table = 'compra_productos';

    protected $fillable = [
        'user_id',
        'producto_id',
        'proveedor_id',
        'cantidad_compra',
        'precio',
        'fecha_compra',
        'admin_id'
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