<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->is_verified) {
            return response()->json(['message' => 'Your account is not verified. Please contact the administrator.'], 403);
        }
        return $next($request);
    }
}
