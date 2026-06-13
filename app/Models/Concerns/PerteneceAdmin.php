<?php

namespace App\Models\Concerns;

use App\Models\Scopes\AdminScope;

trait PerteneceAdmin
{
    protected static function bootPerteneceAdmin(): void
    {
        // 1. Filtra automáticamente las lecturas por admin
        static::addGlobalScope(new AdminScope());

        // 2. Rellena admin_id automáticamente al crear el registro
        static::creating(function ($model) {
            if ($model->admin_id) {
                return; // si ya viene puesto, se respeta (p. ej. seeders)
            }

            $user = auth()->user();
            if (! $user) {
                return; // sin usuario logueado no se toca (seeders, comandos artisan)
            }

            // un trabajador guarda bajo SU admin; un admin guarda bajo sí mismo
            $model->admin_id = $user->rol === 'trabajador'
                ? $user->admin_id
                : $user->id;
        });
    }
}