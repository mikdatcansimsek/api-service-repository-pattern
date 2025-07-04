<?php


namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;


interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active products.
     */
    public function getActiveProducts(): Collection;

    /**
     * Get all products that are in stock.
     */
    public function getInStockProducts(): Collection;

    /**
     * Get products by category ID.
     */
    public function getProductsByCategory(int $categoryId): Collection;

    /**
     * Search products by name.
     */
    public function getProductsByPriceRange(float $minPrice, float $maxPrice): Collection;

    public function findBySku(string $sku) : ?object;

    /**
     * Get all available products (active and in stock).
     */
    public function getAvailableProducts(): Collection;

    /**
     * Search products by name or description.
     */
    public function searchProducts(string $searchTerm): Collection;
}
