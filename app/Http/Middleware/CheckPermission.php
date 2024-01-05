<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, array ...$permissions): Response
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Usuário não autenticado.'
            ], Response::HTTP_UNAUTHORIZED);
        }


        $user = Auth::user();

        if (!$user->permissions()->whereIn('name', $permissions)->exists()) {
            return response()->json([
                'message' => 'Sem permissão'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
