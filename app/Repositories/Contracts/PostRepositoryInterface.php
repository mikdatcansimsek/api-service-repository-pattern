<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Yayınlanmış postları getir
     */
    public function getPublishedPosts(): Collection;

    /**
     * Kullanıcının postlarını getir
     */
    public function getPostsByUser(int $userId): Collection;

    /**
     * Kategoriye göre postları getir
     */
    public function getPostsByCategory(int $categoryId): Collection;

    /**
     * Slug ile post bul
     */
    public function findBySlug(string $slug): ?object;

    /**
     * Son eklenen postları getir
     */
    public function getLatestPosts(int $limit = 10): Collection;

    public function getDraftPosts(): Collection;

    public function searchPosts(string $searchTerm): Collection;

    
}
