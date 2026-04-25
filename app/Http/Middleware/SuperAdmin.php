<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to restrict access to platform administrators only.
 * 
 * Super admins have access to the platform-wide admin panel
 * for managing beta requests, viewing all office data, feedback,
 * and platform analytics.
 */
class SuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_super_admin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            abort(403, 'Access denied. This area is restricted to platform administrators.');
        }

        return $next($request);
    }
}

