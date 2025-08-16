<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PostResource extends CustomResource
{
    
    public function toArray(Request $request): array
    {
        return [
            // Resource identifier (ID, type otomatik)
            ...$this->getResourceIdentifier(),

            // Temel post bilgileri
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'content' => $this->resource->content,
            'excerpt' => $this->resource->excerpt,

            // Post durumu
            'status' => [
                'is_published' => $this->resource->is_published,
                'status_text' => $this->resource->is_published ? 'Yayında' : 'Taslak',
                'color_class' => $this->resource->is_published ? 'success' : 'warning',
                'published_at' => $this->when(
                    $this->resource->published_at,
                    $this->formatDate($this->resource->published_at)
                )
            ],

            // İstatistikler - Model'dan pre-calculated olarak geliyor
            'statistics' => $this->resource->statistics,
            
            // Okuma süresi - Model'dan cache'li olarak geliyor
            'reading_time' => $this->resource->reading_time,

            // Kategoriler ve etiketler
            'category' => $this->loadRelationship('category', CategoryResource::class),
            'tags' => $this->when(
                $this->resource->relationLoaded('tags'),
                $this->resource->tags->pluck('name')
            ),

            // Yazar bilgileri - Sadece relation'dan
            'author' => $this->loadRelationship('user', UserResource::class),

            // Resimler ve medya
            'media' => [
                'featured_image' => $this->resource->featured_image ?? null,
                'gallery' => $this->when(
                    isset($this->resource->gallery) && is_array($this->resource->gallery),
                    $this->resource->gallery
                ),
                'attachments' => $this->when(
                    $this->resource->relationLoaded('attachments'),
                    $this->resource->attachments
                )
            ],

            // SEO bilgileri
            'seo' => [
                'meta_title' => $this->resource->meta_title ?? $this->resource->title,
                'meta_description' => $this->resource->meta_description ?? $this->resource->excerpt,
                'meta_keywords' => $this->resource->meta_keywords,
                'canonical_url' => null,
                'og_image' => $this->resource->featured_image ?? null
            ],

            // Kullanıcı etkileşimleri - Service'den pre-calculated olarak geliyor
            'user_interaction' => $this->resource->user_interaction,

            // Admin bilgileri - Model'dan pre-calculated olarak geliyor  
            'admin_data' => $this->whenAuth($this->resource->admin_data),

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

            // İlişkili postlar
            'related_posts' => $this->when(
                $this->resource->relationLoaded('relatedPosts'),
                PostResource::collection($this->resource->relatedPosts)
            ),

            // API Links - Route bağımlılığı azaltıldı
            'links' => $this->getApiLinks(),

            // Timestamps (formatlanmış)
            ...$this->getTimestamps(),
        ];
    }

    /**
     * API Links - Route bağımlılığını azalt
     */
    private function getApiLinks(): array
    {
        try {
            return [
                'self' => route('api.posts.show', $this->resource->id),
                'author' => route('api.users.show', $this->resource->user_id),
                'category' => route('api.categories.show', $this->resource->category_id),
                'comments' => route('api.posts.comments', $this->resource->id),
                'public_url' => null
            ];
        } catch (\Exception $e) {
            // Route'lar yoksa boş array döndür
            return [
                'self' => null,
                'author' => null,
                'category' => null,
                'comments' => null,
                'public_url' => null
            ];
        }
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
