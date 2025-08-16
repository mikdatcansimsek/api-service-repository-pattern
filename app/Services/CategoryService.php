<?php

namespace App\Services;

use App\Abstracts\BaseService;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryService extends BaseService
{



    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Aktif kategorileri getir
     */
    public function getActiveCategories(): Collection
    {
        return $this->repository->getActiveCategories();
    }

    /**
     * Slug ile kategori bul
     */
    public function findCategoryBySlug(string $slug): ?object
    {
        if (empty(trim($slug))) {
            throw new \InvalidArgumentException("Slug cannot be empty");
        }

        return $this->repository->findBySlug($slug);
    }

    /**
     * Ürün sayısı ile kategorileri getir
     */
    public function getCategoriesWithProductCount(): Collection
    {
        return $this->repository->getCategoriesWithProductCount();
    }

    /**
     * Post sayısı ile kategorileri getir
     */
    public function getCategoriesWithPostCount(): Collection
    {
        return $this->repository->getCategoriesWithPostCount();
    }

    /**
     * Create validation override
     */
    protected function validateCreateData(array $data): ?array
    {
        // Slug oluştur
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Slug benzersizlik kontrolü
        if (isset($data['slug'])) {
            $existingCategory = $this->repository->findBySlug($data['slug']);
            if ($existingCategory) {
                $data['slug'] = $data['slug'] . '-' . time();
            }
        }

        return $data;
    }

    /**
     * Update validation override
     */
    protected function validateUpdateData(int $id, array $data): ?array
    {
        // Slug güncelle
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Slug benzersizlik kontrolü (mevcut kayıt hariç)
        if (isset($data['slug'])) {
            $existingCategory = $this->repository->findBySlug($data['slug']);
            if ($existingCategory && $existingCategory->id !== $id) {
                $data['slug'] = $data['slug'] . '-' . time();
            }
        }

        return $data;
    }
}
