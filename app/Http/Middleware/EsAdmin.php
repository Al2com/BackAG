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

        // solo pasan admin y superadmin: cualquier otro rol (o sin sesión) no
        if (! $user || ! in_array($user->rol, ['admin', 'superadmin'], true)) {
            return response()->json(['mensaje' => 'No autorizado'], 403);
        }

        return $next($request);
    }
}
