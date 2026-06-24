<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\PerteneceAdmin;

class Explotacion extends Model
{
    use PerteneceAdmin;

    protected $table = 'explotaciones';

    protected $fillable = [
        'nombre',
        'ubicacion',
        'descripcion',
        // 'admin_id',
        'propietario_id',
    ];

    public function admin(){
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function propietario(){
        return $this->belongsTo(Propietario::class);
    }

    public function parcelas(){
        return $this->hasMany(Parcela::class);
    }
}