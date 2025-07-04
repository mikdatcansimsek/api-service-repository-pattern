<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Aktif kategorileri getir
     */
    public function getActiveCategories(): Collection;

    /**
     * Slug ile kategori bul
     */
    public function findBySlug(string $slug): ?object;

    public function getCategoriesWithProductCount(): Collection;
    public function getCategoriesWithPostCount(): Collection;
}
