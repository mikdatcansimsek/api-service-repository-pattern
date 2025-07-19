<?php

namespace App\Exceptions;

class ProductNotFoundException extends CustomException
{
    protected int $statusCode = 404;
    protected string $errorCode = 'PRODUCT_NOT_FOUND';
    protected string $logLevel = 'warning';

    public function __construct(int $productId, array $details = [])
    {
        $message = "ID {$productId} olan ürün bulunamadı.";

        $details = array_merge([
            'product_id' => $productId,
            'suggestion' => 'Ürün ID\'sini kontrol edin veya mevcut ürünler listesini görüntüleyin.',
            'help_url' => '/api/products'
        ], $details);

        parent::__construct($message, 404, null, $details);
    }
}
