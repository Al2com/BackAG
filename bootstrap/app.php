<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    // ->withRouting(
    //     web: __DIR__.'/../routes/web.php',
    //     commands: __DIR__.'/../routes/console.php',
    //     health: '/up',
    // )
    ->withRouting(
            web: __DIR__.'/../routes/web.php',
            api: __DIR__.'/../routes/api.php',
            commands: __DIR__.'/../routes/console.php',
            health: '/up',
    )

        ->withMiddleware(function (Middleware $middleware): void {
                $middleware->alias([
                    'admin' => \App\Http\Middleware\EsAdmin::class,
                ]);

                // La app corre detrás del proxy de Railway: sin esto Laravel ve
                // la IP del proxy para todos los clientes, y el throttle por IP
                // del login se comparte entre todos los usuarios.
                $middleware->trustProxies(at: '*');

                $middleware->append(\App\Http\Middleware\CabecerasSeguridad::class);

                // Ya no hay pantalla de login en el back (la tiene el front):
                // una petición sin autenticar no redirige, recibe un 401.
                $middleware->redirectGuestsTo(fn () => null);
            })

    ->withExceptions(function (Exceptions $exceptions): void {
        // La API responde siempre en JSON. Sin esto, una petición sin token y
        // sin cabecera Accept intentaría redirigir a la ruta 'login', que ya
        // no existe, y acabaría en un error 500 en vez de un 401.
        $exceptions->shouldRenderJsonWhen(
            fn ($request) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
