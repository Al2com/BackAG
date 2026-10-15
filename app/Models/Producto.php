<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\PerteneceAdmin;

class Producto extends Model
{
    use PerteneceAdmin;

    protected $fillable = [
        'nombre',
        'materia_activa',
        'categoria',
        'precio',
        'ubicacion',
        'stock_minimo',
        'stock_maximo',
        'stock_actual',
        'unidad',
        'dosis_recomendada',
    ];

    // el stock se maneja con decimales (compras/consumos con decimales)
    protected $casts = [
        'stock_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'stock_maximo' => 'decimal:2',
    ];

    public function Fumigacion(){
        return $this->belongsToMany(Fumigacion::class, 'fumigacion_producto')
          ->withPivot('cantidad', 'dosis_introducida');
    }

    // Punto único para descontar consumo del almacén (abonado y fumigaciones
    // lo llaman igual). Nunca deja el stock en negativo. Si en el futuro se
    // quiere un histórico de movimientos, este es el sitio donde registrarlo.
    public function descontarStock(float $cantidad): void
    {
        $this->stock_actual = max(0, $this->stock_actual - $cantidad);
        $this->save();
    }
}