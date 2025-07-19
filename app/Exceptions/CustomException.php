<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;    // ← EKLE
use Illuminate\Support\Str;

/**
 * Custom Base Exception Class
 */
class CustomException extends Exception
{
    protected int $statusCode = 500;
    protected string $errorCode = 'GENERIC_ERROR';
    protected string $errorType = 'CustomException';
    protected array $errorDetails = [];
    protected bool $shouldLog = true;
    protected string $logLevel = 'error';

    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        array $details = []
    ) {
        parent::__construct($message, $code, $previous);

        $this->errorDetails = $details;
        $this->errorCode = $this->getDefaultErrorCode();
        $this->errorType = $this->getDefaultErrorType();

        if ($this->shouldLog) {
            $this->logException();
        }
    }

    public function render(Request $request): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => [
                'type' => $this->errorType,
                'code' => $this->errorCode,
                'message' => $this->getMessage(),
                'status_code' => $this->statusCode,
            ],
            'meta' => [
                'timestamp' => now()->toISOString(),
                'request_id' => $this->generateRequestId(),
                'endpoint' => $request->getPathInfo(),
                'method' => $request->getMethod(),
            ]
        ];

        if (!empty($this->errorDetails)) {
            $response['error']['details'] = $this->errorDetails;
        }

        if (config('app.debug')) {
            $response['debug'] = [
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'trace' => collect($this->getTrace())->take(3)->toArray(),
            ];
        }

        if (Auth::check()) {
            $response['meta']['user_id'] = Auth::id();
        }

        return response()->json($response, $this->statusCode);
    }

    protected function getDefaultErrorCode(): string
    {
        return strtoupper(Str::snake(class_basename($this)));
    }

    protected function getDefaultErrorType(): string
    {
        return class_basename($this);
    }

    protected function logException(): void
    {
        $context = [
            'exception_type' => get_class($this),
            'error_code' => $this->errorCode,
            'status_code' => $this->statusCode,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'details' => $this->errorDetails,
        ];

        Log::log($this->logLevel, $this->getMessage(), $context);
    }

    protected function generateRequestId(): string
    {
        return 'req_' . uniqid() . '_' . time();
    }

    public function setDetails(array $details): self
    {
        $this->errorDetails = array_merge($this->errorDetails, $details);
        return $this;
    }

    public function setErrorCode(string $code): self
    {
        $this->errorCode = $code;
        return $this;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }
}
