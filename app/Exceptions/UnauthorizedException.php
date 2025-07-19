<?php

namespace App\Exceptions;

class UnauthorizedException extends CustomException
{
    protected int $statusCode = 401;
    protected string $errorCode = 'UNAUTHORIZED';
    protected string $logLevel = 'warning';

    public function __construct(string $message = 'Bu işlem için yetkiniz bulunmuyor.', array $details = [])
    {
        $details = array_merge([
            'required_permission' => 'authenticated',
            'suggestion' => 'Lütfen giriş yapın veya geçerli bir API token kullanın.',
            'login_url' => '/api/auth/login'
        ], $details);

        parent::__construct($message, 401, null, $details);
    }
}
