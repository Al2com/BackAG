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
        'precio',
        'ubicacion',
        'stock_minimo',
        'stock_actual',
        'unidad',
        'dosis_recomendada',
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