<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\PerteneceAdmin;

class Recoleccion extends Model{
    use PerteneceAdmin;

    protected $table = 'recoleccion';

    protected $fillable = [
        'parcela_id',
        'fecha',
        'tipo',
        'kilos',
        'precio_medio_kg',
        'variedad',
        // 'admin_id',
    ];

    public function parcela()
    {
        return $this->belongsTo(Parcela::class);
    }
}