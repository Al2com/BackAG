<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\PerteneceAdmin;

class RiegoManta extends Model
{
    use PerteneceAdmin;

    protected $table = 'riegos_manta';

    protected $fillable = [
        'parcela_id',
        'lote_id',
        'fecha',
        'precio_por_hanegada',
        'hanegadas',
        'importe',
        // 'admin_id',
    ];

    public function parcela()
    {
        return $this->belongsTo(Parcela::class);
    }
}
