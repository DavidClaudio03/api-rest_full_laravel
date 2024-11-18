<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
{
    // Obtener el encabezado de autorización
    $token = $request->header('Authorization');

    // Inicializar JwtAuth y verificar el token
    $jwtAuth = new \App\Helpers\JwtAuth();
    $checkToken = $jwtAuth->checkToken($token);

    // Si el token es inválido, devolver una respuesta de error
    if (!$checkToken) {
        return response()->json([
            'code' => 401,
            'status' => 'error',
            'message' => 'Acceso no autorizado. Token inválido.'
        ], 401);
    }

    // Si el token es válido, permitir que la solicitud continúe
    return $next($request);
}

}
