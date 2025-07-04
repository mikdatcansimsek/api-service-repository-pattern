<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function findByWhere(array $criteria): Collection
    {
        return $this->model->where($criteria)->get();
    }

    public function getActiveCategories(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findBySlug(string $slug): ?object
    {
        return $this->model->where('slug', $slug)->first();
    }
    public function getCategoriesWithProductCount(): Collection
    {
        return $this->model->where('is_active', true)
            ->withCount(['products' => function($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();
    }


    public function getCategoriesWithPostCount(): Collection
    {
        return $this->model->where('is_active', true)
            ->withCount(['posts' => function($query) {
                $query->where('is_published', true);
            }])
            ->orderBy('name')
            ->get();
    }
}
