<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\PerteneceAdmin;

class Propietario extends Model
{
    use PerteneceAdmin;

    protected $fillable = [
        'nombre',
        'dni',
        'telefono',
        'admin_id',
    ];

    public function explotaciones(){
        return $this->hasMany(Explotacion::class);
    }

    public function parcelas(){
        return $this->hasMany(Parcela::class);
    }
}