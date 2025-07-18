<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PostResource extends CustomResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            // Resource identifier (ID, type otomatik)
            ...$this->getResourceIdentifier(),

            // Temel post bilgileri
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'excerpt' => $this->resource->excerpt,

            // İçerik (sadece yayınlanmış veya sahibi görür)
            'content' => $this->when(
                $this->resource->is_published || $this->isOwner(),
                $this->resource->content
            ),

            // Yayın durumu
            'publication_status' => [
                'is_published' => $this->resource->is_published,
                'status_text' => $this->resource->is_published ? 'Yayında' : 'Taslak',
                'published_at' => $this->when(
                    $this->resource->published_at,
                    $this->formatDate($this->resource->published_at)
                ),
                'color_class' => $this->resource->is_published ? 'success' : 'warning'
            ],

            // Yazar bilgileri
            'author' => $this->loadRelationship('user', UserResource::class),

            // Kategori bilgileri
            'category' => $this->loadRelationship('category', CategoryResource::class),

            // Etiketler (eğer varsa)
            'tags' => $this->when(
                $this->resource->relationLoaded('tags'),
                $this->resource->tags->pluck('name') ?? []
            ),

            // İstatistikler
            'statistics' => [
                'view_count' => $this->resource->view_count ?? 0,
                'like_count' => $this->resource->likes_count ?? 0,
                'comment_count' => $this->resource->comments_count ?? 0,
                'share_count' => $this->resource->shares_count ?? 0,
                'reading_time' => $this->calculateReadingTime()
            ],

            // Medya dosyaları (eğer varsa)
            'media' => [
                'featured_image' => $this->resource->featured_image,
                'gallery' => $this->when(
                    $this->resource->relationLoaded('media'),
                    $this->resource->media->map(function ($media) {
                        return [
                            'id' => $media->id,
                            'url' => $media->url,
                            'type' => $media->type,
                            'alt_text' => $media->alt_text,
                        ];
                    })
                )
            ],

            // SEO bilgileri
            'seo' => [
                'meta_title' => $this->resource->meta_title ?? $this->resource->title,
                'meta_description' => $this->resource->meta_description ?? $this->resource->excerpt,
                'meta_keywords' => $this->resource->meta_keywords,
                'canonical_url' => route('posts.show', $this->resource->slug ?? $this->resource->id),
                'og_image' => $this->resource->featured_image ?? null
            ],

            // Kullanıcı etkileşimleri (giriş yapmış kullanıcılar için)
            'user_interaction' => $this->whenAuth([
                'is_liked' => $this->isLikedByUser(),
                'is_bookmarked' => $this->isBookmarkedByUser(),
                'user_rating' => $this->getUserRating(),
                'can_edit' => $this->canUserEdit(),
                'can_delete' => $this->canUserDelete()
            ]),

            // Yazar verileri (sadece yazarın kendisi görür)
            'author_data' => $this->whenOwner([
                'draft_notes' => $this->resource->draft_notes,
                'scheduled_at' => $this->when(
                    $this->resource->scheduled_at,
                    $this->formatDate($this->resource->scheduled_at)
                ),
                'last_edited_at' => $this->when(
                    $this->resource->last_edited_at,
                    $this->formatDate($this->resource->last_edited_at)
                )
            ]),

            // Admin bilgileri
            'admin_data' => $this->whenAuth([
                'is_featured' => $this->resource->is_featured ?? false,
                'is_pinned' => $this->resource->is_pinned ?? false,
                'moderation_status' => $this->resource->moderation_status ?? 'approved',
                'admin_notes' => $this->resource->admin_notes
            ]),

            // İlişkili postlar
            'related_posts' => $this->when(
                $this->resource->relationLoaded('relatedPosts'),
                PostResource::collection($this->resource->relatedPosts)->withoutMeta()
            ),

            // API Links
            'links' => [
                'self' => route('api.posts.show', $this->resource->id),
                'author' => route('api.users.show', $this->resource->user_id),
                'category' => route('api.categories.show', $this->resource->category_id),
                'comments' => route('api.posts.comments', $this->resource->id) ?? '#',
                'public_url' => route('posts.show', $this->resource->slug ?? $this->resource->id) ?? '#'
            ],

            // Timestamps (formatlanmış)
            ...$this->getTimestamps(),
        ];
    }

    /**
     * Okuma süresini hesapla
     */
    private function calculateReadingTime(): array
    {
        $wordCount = str_word_count(strip_tags($this->resource->content ?? ''));
        $readingTimeMinutes = max(1, ceil($wordCount / 200)); // 200 kelime/dakika

        return [
            'word_count' => $wordCount,
            'minutes' => $readingTimeMinutes,
            'formatted' => $readingTimeMinutes . ' dk okuma'
        ];
    }

    /**
     * Kullanıcının bu postu beğenip beğenmediğini kontrol et
     */
    private function isLikedByUser(): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        // Bu method'u Post model'inde implement etmeniz gerekiyor
        return method_exists($this->resource, 'isLikedByUser')
            ? $this->resource->isLikedByUser($this->getAuthUserId())
            : false;
    }

    /**
     * Kullanıcının bu postu kaydetip kaydetmediğini kontrol et
     */
    private function isBookmarkedByUser(): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        return method_exists($this->resource, 'isBookmarkedByUser')
            ? $this->resource->isBookmarkedByUser($this->getAuthUserId())
            : false;
    }

    /**
     * Kullanıcının bu post için verdiği rating
     */
    private function getUserRating(): ?int
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return method_exists($this->resource, 'getUserRating')
            ? $this->resource->getUserRating($this->getAuthUserId())
            : null;
    }

    /**
     * Kullanıcı bu postu düzenleyebilir mi?
     */
    private function canUserEdit(): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        return $this->getAuthUserId() === $this->resource->user_id ||
               ($this->getAuthUser() && method_exists($this->getAuthUser(), 'can') && $this->getAuthUser()->can('edit', $this->resource));
    }

    /**
     * Kullanıcı bu postu silebilir mi?
     */
    private function canUserDelete(): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        return $this->getAuthUserId() === $this->resource->user_id ||
               ($this->getAuthUser() && method_exists($this->getAuthUser(), 'can') && $this->getAuthUser()->can('delete', $this->resource));
    }

    /**
     * Bu post'un sahibi mi kontrol et
     */
    private function isOwner(): bool
    {
        return $this->isAuthenticated() && $this->getAuthUserId() === $this->resource->user_id;
    }

    /**
     * Post için özel meta bilgileri
     */
    public function with(Request $request): array
    {
        return array_merge(parent::with($request), [
            'post_meta' => [
                'type' => 'post',
                'content_type' => 'article',
                'language' => 'tr',
                'reading_difficulty' => 'medium',
                'content_warning' => $this->resource->content_warning ?? null
            ]
        ]);
    }
}
