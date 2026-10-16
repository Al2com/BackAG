<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\CompraProducto;
use App\Observers\CompraProductoObserver;

class AppServiceProvider extends ServiceProvider{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        // el stock entra/sale al crear/borrar una compra (ver observer)
        CompraProducto::observe(CompraProductoObserver::class);

        ResetPassword::createUrlUsing(function ($user, string $token) {
        $frontend = config('app.frontend_url');
        return $frontend . '/reset-password?token=' . $token
             . '&email=' . urlencode($user->getEmailForPasswordReset());
    });
    }
}
