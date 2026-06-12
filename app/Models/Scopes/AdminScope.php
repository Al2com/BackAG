<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AdminScope implements Scope
{
    /**
 * Scope global para filtrar datos por admin.
 * 
 * - Trabajador: ve solo los datos de su admin (admin_id = su admin_id)
 * - Admin: ve solo sus propios datos (admin_id = su id)
 * - Superadmin: ve todo, sin filtro
 * 
 * Se aplica automáticamente en todos los modelos que lo incluyan,
 * sin necesidad de escribir el WHERE en cada controlador.
 */
public function apply(Builder $builder, Model $model): void
{
    $user = auth()->user();

    if ($user && $user->rol === 'trabajador') {
        $builder->where('admin_id', $user->admin_id);
    } elseif ($user && $user->rol === 'admin') {
        $builder->where('admin_id', $user->id);
    }
    // superadmin ve todo, no filtra
}
}
