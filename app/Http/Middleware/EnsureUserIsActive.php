<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Si el usuario está activo, permitir acceso
        if ($user->is_active) {
            return $next($request);
        }

        // Rutas permitidas para usuarios inactivos
        $allowedRoutes = [
            'approval.pending',
            'logout',
            'user-profile-information.update', // Ruta de Fortify para actualizar perfil
            'profile.edit',
            'profile.update',
            'sanctum.csrf-cookie',
        ];

        // Permitir peticiones de Inertia para compartir datos (necesario para el frontend)
        if ($request->header('X-Inertia')) {
             // Si es una navegación parcial o recarga, verificar si es a una ruta permitida
             // Esto es complejo de verificar por nombre de ruta en middleware, 
             // así que confiamos en la redirección de abajo para rutas completas.
        }

        if ($request->routeIs($allowedRoutes)) {
            return $next($request);
        }

        // Si intenta acceder a cualquier otra cosa, redirigir a pendiente
        return redirect()->route('approval.pending');
    }
}
