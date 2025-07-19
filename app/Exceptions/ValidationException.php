<?php

namespace App\Exceptions;

class ValidationException extends CustomException
{
    protected int $statusCode = 422;
    protected string $errorCode = 'VALIDATION_ERROR';
    protected string $logLevel = 'info';

    public function __construct(array $errors, string $message = 'Validation failed', array $details = [])
    {
        $details = array_merge([
            'errors' => $errors,
            'error_count' => count($errors),
            'suggestion' => 'Lütfen form verilerinizi kontrol edin ve tekrar deneyin.'
        ], $details);

        parent::__construct($message, 422, null, $details);
    }
}
