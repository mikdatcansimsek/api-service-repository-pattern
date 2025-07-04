<?php

namespace App\Services;

use App\Abstracts\BaseService;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class PostService extends BaseService
{


    public function __construct(PostRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Yayınlanmış postları getir
     */
    public function getPublishedPosts(): Collection
    {
        return $this->repository->getPublishedPosts();
    }

    /**
     * Kullanıcının postlarını getir
     */
    public function getPostsByUser(int $userId): Collection
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException("User ID must be positive");
        }

        return $this->repository->getPostsByUser($userId);
    }

    /**
     * Kategoriye göre postları getir
     */
    public function getPostsByCategory(int $categoryId): Collection
    {
        if ($categoryId <= 0) {
            throw new \InvalidArgumentException("Category ID must be positive");
        }

        return $this->repository->getPostsByCategory($categoryId);
    }

    /**
     * Slug ile post bul
     */
    public function findPostBySlug(string $slug): ?object
    {
        if (empty(trim($slug))) {
            throw new \InvalidArgumentException("Slug cannot be empty");
        }

        return $this->repository->findBySlug($slug);
    }

    /**
     * Son postları getir
     */
    public function getLatestPosts(int $limit = 10): Collection
    {
        if ($limit <= 0 || $limit > 100) {
            throw new \InvalidArgumentException("Limit must be between 1 and 100");
        }

        return $this->repository->getLatestPosts($limit);
    }

    /**
     * Taslak postları getir
     */
    public function getDraftPosts(): Collection
    {
        return $this->repository->getDraftPosts();
    }

    /**
     * Post arama
     */
    public function searchPosts(string $searchTerm): Collection
    {
        if (strlen(trim($searchTerm)) < 2) {
            throw new \InvalidArgumentException("Search term must be at least 2 characters");
        }

        return $this->repository->searchPosts($searchTerm);
    }

    /**
     * Post yayınla
     */
    public function publishPost(int $postId): object
    {
        $post = $this->getRecordById($postId);

        if ($post->is_published) {
            throw new \Exception("Post is already published");
        }

        return $this->repository->update($postId, [
            'is_published' => true,
            'published_at' => now()
        ]);
    }

    /**
     * Post yayından kaldır
     */
    public function unpublishPost(int $postId): object
    {
        $post = $this->getRecordById($postId);

        if (!$post->is_published) {
            throw new \Exception("Post is already unpublished");
        }

        return $this->repository->update($postId, [
            'is_published' => false,
            'published_at' => null
        ]);
    }

    /**
     * Create validation override
     */
    protected function validateCreateData(array $data): ?array
    {
        // Slug oluştur
        if (isset($data['title']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Slug benzersizlik kontrolü
        if (isset($data['slug'])) {
            $existingPost = $this->repository->findBySlug($data['slug']);
            if ($existingPost) {
                $data['slug'] = $data['slug'] . '-' . time();
            }
        }

        // Excerpt oluştur (eğer yoksa)
        if (isset($data['content']) && !isset($data['excerpt'])) {
            $data['excerpt'] = Str::limit(strip_tags($data['content']), 150);
        }

        // User ID kontrolü (API'de genelde auth user'dan gelir)
        if (!isset($data['user_id'])) {
            throw new \InvalidArgumentException("User ID is required");
        }

        // Category ID kontrolü
        if (!isset($data['category_id'])) {
            throw new \InvalidArgumentException("Category ID is required");
        }

        // Yayın tarihi kontrolü
        if (isset($data['is_published']) && $data['is_published'] && !isset($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    /**
     * Update validation override
     */
    protected function validateUpdateData(int $id, array $data): ?array
    {
        // Slug güncelle
        if (isset($data['title']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Slug benzersizlik kontrolü (mevcut kayıt hariç)
        if (isset($data['slug'])) {
            $existingPost = $this->repository->findBySlug($data['slug']);
            if ($existingPost && $existingPost->id !== $id) {
                $data['slug'] = $data['slug'] . '-' . time();
            }
        }

        // Excerpt güncelle
        if (isset($data['content']) && !isset($data['excerpt'])) {
            $data['excerpt'] = Str::limit(strip_tags($data['content']), 150);
        }

        // Yayın durumu değişikliği
        if (isset($data['is_published'])) {
            if ($data['is_published'] && !isset($data['published_at'])) {
                $data['published_at'] = now();
            } elseif (!$data['is_published']) {
                $data['published_at'] = null;
            }
        }

        return $data;
    }

    /**
     * Delete validation override
     */
    protected function validateDeleteOperation(int $id): void
    {
        $post = $this->getRecordById($id);

        // İş kuralı: Yayınlanmış post silinemez (isteğe bağlı)
        if ($post->is_published) {
            throw new \Exception("Cannot delete published post. Unpublish it first.");
        }
    }
}
