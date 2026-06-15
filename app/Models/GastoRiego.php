<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\PerteneceAdmin;

class GastoRiego extends Model
{
    use PerteneceAdmin;

    protected $table = 'gastos_riego';

    protected $fillable = [
        'parcela_id',
        'anio',
        'mes',
        'concepto',
        'importe',
        'admin_id',
    ];

    public function parcela()
    {
        return $this->belongsTo(Parcela::class);
    }
}