<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response =$next($request);

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);

            if(!isset($data['success']) && !isset($data['error'])) {
                $wrappedData = [
                    'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
                    'data' => $data,
                    'message' => $this->getStatusMessage($response->getStatusCode()),
                    'timestamp' => now()->toIsoString(),
                ];
                $response->setData($wrappedData);
            }

        }
        return $response;
    }

    private function getStatusMessage(int $statusCode): string
    {
        return match ($statusCode) {
            200 => 'Success',
            201 => 'Created successfully',
            204 => 'Deleted successfully',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            422 => 'Validation error',
            429 => 'Too many requests',
            500 => 'Internal server error',
            default => 'Request processed'
        };
    }
}
