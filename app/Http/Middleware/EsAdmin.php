<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
   public function handle(Request $request, Closure $next): Response{
        $user = $request->user();

        // un trabajador (o sin sesión) no pasa
        if (! $user || $user->rol === 'trabajador') {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        return $next($request);
    }
}
