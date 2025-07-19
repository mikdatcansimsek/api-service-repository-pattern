<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\CustomException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\ValidationException;
use App\Exceptions\UnauthorizedException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        // Handle API requests
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    protected function handleApiException(Request $request, Throwable $e): JsonResponse
    {
        // Handle CustomExceptions
        if ($e instanceof CustomException) {
            return $e->render($request);
        }

        // Convert common Laravel exceptions
        if ($e instanceof ModelNotFoundException) {
            return $this->handleModelNotFoundException($e, $request);
        }

        if ($e instanceof LaravelValidationException) {
            $exception = new ValidationException($e->errors(), 'Validation failed');
            return $exception->render($request);
        }

        if ($e instanceof AuthenticationException) {
            $exception = new UnauthorizedException('Bu işlem için giriş yapmanız gerekiyor.');
            return $exception->render($request);
        }

        // Fallback for other exceptions
        return $this->renderGenericException($request, $e);
    }

    protected function handleModelNotFoundException(ModelNotFoundException $e, Request $request): JsonResponse
    {
        $model = $e->getModel();
        $ids = $e->getIds();
        $id = is_array($ids) ? $ids[0] : $ids;

        if ($model === 'App\\Models\\Product') {
            $exception = new ProductNotFoundException((int)$id);
        } elseif ($model === 'App\\Models\\Category') {
            $exception = new CategoryNotFoundException((int)$id);
        } else {
            $exception = new CustomException(
                class_basename($model) . ' bulunamadı.',
                404,
                $e,
                ['model' => class_basename($model), 'id' => $id]
            );
            $exception->setStatusCode(404)->setErrorCode('RESOURCE_NOT_FOUND');
        }

        return $exception->render($request);
    }

    protected function renderGenericException(Request $request, Throwable $e): JsonResponse
    {
        $statusCode = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
            ? $e->getStatusCode()
            : 500;

        $response = [
            'success' => false,
            'error' => [
                'type' => class_basename($e),
                'code' => 'GENERIC_ERROR',
                'message' => config('app.debug') ? $e->getMessage() : 'Bir hata oluştu.',
                'status_code' => $statusCode,
            ],
            'meta' => [
                'timestamp' => now()->toISOString(),
                'request_id' => 'req_' . uniqid(),
                'endpoint' => $request->getPathInfo(),
                'method' => $request->getMethod(),
            ]
        ];

        if (config('app.debug')) {
            $response['debug'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
            ];
        }

        return response()->json($response, $statusCode);
    }
}
