<?php

namespace App\Exceptions;

use Exception;

class DatabaseException extends Exception
{
    protected $context;

    public function __construct(string $operation, Exception $previous = null, array $context = [])
    {
        $this->context = $context;
        parent::__construct("Database error during {$operation}", 500, $previous);
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
