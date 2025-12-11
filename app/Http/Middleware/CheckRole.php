<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($role === 'superadmin' && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access');
        }

        if ($role === 'admin' && !$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access');
        }

        if ($role === 'reseller' && !$user->isReseller() && !$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}

