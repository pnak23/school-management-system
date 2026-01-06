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
     * Check if the authenticated user has one of the specified roles.
     * If not, return 403 Forbidden or redirect based on request type.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  One or more role names (e.g., 'admin', 'manager')
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login first.'
                ], 401);
            }

            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = auth()->user();

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            // Get user's actual roles for better error message
            $userRoles = $user->roles->pluck('name')->toArray();
            $requiredRoles = implode(', ', $roles);
            $currentRoles = !empty($userRoles) ? implode(', ', $userRoles) : 'none';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. You do not have the required role.',
                    'required_roles' => $roles,
                    'your_roles' => $userRoles
                ], 403);
            }

            // For web requests, redirect or show error page
            abort(403, "Access Denied. Required role(s): {$requiredRoles}. Your role(s): {$currentRoles}");
        }

        return $next($request);
    }
}











