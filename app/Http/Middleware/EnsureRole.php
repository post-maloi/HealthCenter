<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        if (!$user->is_active) {
            abort(403, 'Your account is inactive.');
        }

        if (empty($roles)) {
            return $next($request);
        }

        $currentRole = strtolower(trim((string) ($user->role ?? '')));
        $allowedRoles = array_map(fn (string $role) => strtolower(trim($role)), $roles);

        if (!in_array($currentRole, $allowedRoles, true)) {
            abort(403);
        }

        return $next($request);
    }
}

