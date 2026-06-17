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
        'admin_id'
    ];

    public function Fumigacion(){
        return $this->belongsToMany(Fumigacion::class, 'fumigacion_producto')
          ->withPivot('cantidad', 'dosis_introducida');
    }

    
}