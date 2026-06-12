<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\AdminScope;

class Fumigacion extends Model
{

    protected $table = 'fumigaciones';
    protected $fillable = [
    'parcela_id',
    'operacion_id',     // lo añadi para la herencia con operaciones, puede ser nulo
    'usuario_id',
    'operario',
    'estado',           // pendiente, realizada o revisada  faltaba y petaba al crear
    'metodo_aplicacion',
    'hora_inicio',
    'duracion_minutos',
    'mochilas',
    'precio',
    'num_parcelas',
    'turbos',
    'descripcion',
    'admin_id'
];

    // Fumigacion hereda de Operaciones, operacion_id puede ser null
    // si la fumigacion no viene de una operacion previa
    public function Operaciones(){
        return $this->belongsTo(Operacion::class);
    }


    public function Productos(){
        return $this->belongsToMany(Producto::class, 'fumigacion_producto')
        ->withPivot('cantidad','dosis_introducida');
    }

    public function parcela(){
    return $this->belongsTo(Parcela::class, 'parcela_id');
}

    protected static function booted(): void{
        static::addGlobalScope(new AdminScope());
    }
}