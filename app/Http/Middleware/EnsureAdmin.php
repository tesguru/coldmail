<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->guest()) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Unauthenticated'], 401)
                : redirect()->route('login');
        }

        if (! auth()->user()->is_admin) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Forbidden. Admin only.'], 403)
                : abort(403);
        }

        return $next($request);
    }
}