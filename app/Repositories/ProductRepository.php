<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getActiveProducts(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function getInStockProducts(): Collection
    {
        return $this->model->where('quantity', '>', 0)->get();
    }

    public function getProductsByCategory(int $categoryId): Collection
    {
        return $this->model->where('category_id', $categoryId)
                          ->with('category')
                          ->get();
    }

    public function getProductsByPriceRange(float $minPrice, float $maxPrice): Collection
    {
        return $this->model->whereBetween('price', [$minPrice, $maxPrice])
                          ->get();
    }

    public function findBySku(string $sku): ?object
    {
        return $this->model->where('sku', $sku)->first();
    }

    public function getAvailableProducts(): Collection
    {
        return $this->model->where('is_active', true)
                          ->where('quantity', '>', 0)
                          ->with('category')
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    public function searchProducts(string $searchTerm): Collection
    {
        return $this->model->where(function($query) use ($searchTerm) {
                               $query->where('name', 'like', "%{$searchTerm}%")
                                     ->orWhere('description', 'like', "%{$searchTerm}%");
                           })
                          ->where('is_active', true)
                          ->with('category')
                          ->get();
    }
}
