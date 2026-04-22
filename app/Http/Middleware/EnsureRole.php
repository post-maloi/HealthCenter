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

        if (empty($roles)) {
            return $next($request);
        }

        if (!in_array((string) $user->role, $roles, true)) {
            abort(403);
        }

        if ((string) $user->role === 'doctor' && !now()->isWednesday()) {
            abort(403, 'Doctor access is available only every Wednesday.');
        }

        return $next($request);
    }
}

