<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\PostRepositoryInterface;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{

    public function __construct(Post $model)
    {
        parent::__construct($model);
    }
    public function findByWhere(array $criteria): Collection
    {
        return $this->model->where($criteria)->with(['user', 'category'])->get();
    }

    public function getPublishedPosts(): Collection
    {
        return $this->model->where('is_published', true)->whereNotNull('published_at')->with('category')->orderBy('published_at', 'desc')->get();
    }

    public function getPostsByUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->with('category')->orderBy('created_at', 'desc')->get();
    }

    public function getPostsByCategory(int $categoryId): Collection
    {
        return $this->model->where('category_id', $categoryId)->with('category')->orderBy('published_at', 'desc')->get();
    }

    public function findBySlug(string $slug): ?object
    {
        return $this->model->where('slug', $slug)->with(['user', 'category'])->first();
    }

    public function getLatestPosts(int $limit = 10): Collection
    {
        return $this->model->where('is_published', true)
            ->whereNotNull('published_at')
            ->with(['user', 'category'])
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }


    /**
     * Taslak postları getir
     */
    public function getDraftPosts(): Collection
    {
        return $this->model->where('is_published', false)
            ->with(['user', 'category'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Post arama
     */
    public function searchPosts(string $searchTerm): Collection
    {
        return $this->model->where(function($query) use ($searchTerm) {
            $query->where('title', 'like', "%{$searchTerm}%")
                ->orWhere('content', 'like', "%{$searchTerm}%");
        })
            ->where('is_published', true)
            ->with(['user', 'category'])
            ->orderBy('published_at', 'desc')
            ->get();
    }
}
