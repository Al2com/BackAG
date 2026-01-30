<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Propietarios extends Model
{
       protected $fillable = [
        'nomnbre',
        'dni',
        'telefono',

    ];

    public function explotaciones(){
        return $this->hasMany(Explotacion::class);
    }

    public function parcelas(){
        return $this->hasMany(Parcela::class);
    }
}
