<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Auth;

class ForbiddenException extends CustomException
{
    protected int $statusCode = 403;
    protected string $errorCode = 'FORBIDDEN';
    protected string $logLevel = 'warning';

    public function __construct(string $action = 'Bu işlemi', array $details = [])
    {
        $message = "{$action} gerçekleştirmek için yetkiniz bulunmuyor.";

        $details = array_merge([
            'action' => $action,
            'suggestion' => 'Bu işlem için gerekli izinlere sahip olduğunuzdan emin olun.',
            'user_id' => Auth::id()  // ← auth()->id() yerine Auth::id()
        ], $details);

        parent::__construct($message, 403, null, $details);
    }
}
