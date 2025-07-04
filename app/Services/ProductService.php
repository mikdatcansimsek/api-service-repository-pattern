<?php

namespace App\Services;

use App\Abstracts\BaseService;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ProductService extends BaseService
{


    public function __construct(ProductRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getActiveProducts(): Collection
    {
        return $this->repository->getActiveProducts();
    }

    public function getInStockProducts(): Collection
    {
        return $this->repository->getInStockProducts();
    }

    public function getAvailableProducts(): Collection
    {
        return $this->repository->getAvailableProducts();
    }

    public function getProductsByCategory(int $categoryId): Collection
    {
        if ($categoryId <= 0) {
            throw new \InvalidArgumentException("Category ID must be positive");
        }

        return $this->repository->getProductsByCategory($categoryId);
    }

    public function searchProducts(string $searchTerm): Collection
    {
        if (strlen(trim($searchTerm)) < 2) {
            throw new \InvalidArgumentException("Search term must be at least 2 characters");
        }

        return $this->repository->searchProducts($searchTerm);
    }

    public function findProductBySku(string $sku): ?object
    {
        if (empty(trim($sku))) {
            throw new \InvalidArgumentException("SKU cannot be empty");
        }

        return $this->repository->findBySku($sku);
    }

    protected function validateCreateData(array $data): ?array
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if (isset($data['price']) && $data['price'] < 0) {
            throw new \InvalidArgumentException("Price cannot be negative");
        }

        return $data;
    }
}
