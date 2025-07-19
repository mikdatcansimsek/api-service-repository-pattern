<?php

namespace App\Exceptions;

class CategoryNotFoundException extends CustomException
{
    protected int $statusCode = 404;
    protected string $errorCode = 'CATEGORY_NOT_FOUND';
    protected string $logLevel = 'warning';

    public function __construct(int $categoryId, array $details = [])
    {
        $message = "ID {$categoryId} olan kategori bulunamadı.";

        $details = array_merge([
            'category_id' => $categoryId,
            'suggestion' => 'Kategori ID\'sini kontrol edin.',
            'help_url' => '/api/categories'
        ], $details);

        parent::__construct($message, 404, null, $details);
    }
}
