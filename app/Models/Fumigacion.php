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
        'operacion_id',
        'usuario_id',
        'operario',
        'estado',
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
}