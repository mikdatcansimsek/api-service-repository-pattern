<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'api_rate_limit:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 100)) { // 100 requests per minute
            return response()->json([
                'error' => 'Too many requests. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }

        RateLimiter::hit($key, 60); // Reset every minute

        $response = $next($request);


        $response->headers->set('X-RateLimit-Limit', 100);
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($key, 100));
        return $response;
    }
}
