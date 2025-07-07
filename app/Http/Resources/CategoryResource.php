<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CategoryResource extends CustomResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            // Resource identifier (ID, type otomatik)
            ...$this->getResourceIdentifier(),
            
            // Temel kategori bilgileri
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'is_active' => $this->resource->is_active,
            
            // Kategori durumu
            'status' => [
                'is_active' => $this->resource->is_active,
                'status_text' => $this->resource->is_active ? 'Aktif' : 'Pasif',
                'color_class' => $this->resource->is_active ? 'success' : 'danger'
            ],
            
            // İstatistikler (eğer relation yüklenmişse)
            'statistics' => [
                'products_count' => $this->when(
                    isset($this->resource->products_count), 
                    $this->resource->products_count ?? 0
                ),
                'posts_count' => $this->when(
                    isset($this->resource->posts_count), 
                    $this->resource->posts_count ?? 0
                ),
                'total_items' => $this->when(
                    isset($this->resource->products_count) || isset($this->resource->posts_count),
                    ($this->resource->products_count ?? 0) + ($this->resource->posts_count ?? 0)
                )
            ],
            
            // Parent-Child ilişkileri (eğer varsa)
            'hierarchy' => $this->when(
                isset($this->resource->parent_id) || isset($this->resource->level),
                [
                    'parent_id' => $this->resource->parent_id ?? null,
                    'level' => $this->resource->level ?? 0,
                    'has_children' => isset($this->resource->children_count) ? $this->resource->children_count > 0 : false,
                    'children_count' => $this->resource->children_count ?? 0
                ]
            ),
            
            // Parent category (eğer yüklenmişse)
            'parent' => $this->loadRelationship('parent', CategoryResource::class),
            
            // Child categories (eğer yüklenmişse)
            'children' => $this->loadRelationship('children', CategoryResource::class),
            
            // SEO bilgileri (eğer varsa)
            'seo' => $this->when(
                isset($this->resource->meta_title) || isset($this->resource->meta_description),
                [
                    'meta_title' => $this->resource->meta_title ?? $this->resource->name,
                    'meta_description' => $this->resource->meta_description ?? $this->resource->description,
                    'canonical_url' => route('categories.show', $this->resource->slug ?? $this->resource->id)
                ]
            ),
            
            // Admin bilgileri (sadece admin için)
            'admin_data' => $this->whenAuth([
                'sort_order' => $this->resource->sort_order ?? 0,
                'internal_notes' => $this->resource->internal_notes ?? null,
                'created_by' => $this->resource->created_by ?? null,
            ]),
            
            // API Links
            'links' => [
                'self' => route('api.categories.show', $this->resource->id),
                'products' => route('api.categories.products', $this->resource->id) ?? '#',
                'posts' => route('api.categories.posts', $this->resource->id) ?? '#'
            ],
            
            // Timestamps (formatlanmış)
            ...$this->getTimestamps(),
        ];
    }

    /**
     * Kategori için özel meta bilgileri
     */
    public function with(Request $request): array
    {
        return array_merge(parent::with($request), [
            'category_meta' => [
                'type' => 'category',
                'hierarchy_enabled' => config('categories.hierarchy_enabled', true),
                'max_depth' => config('categories.max_depth', 3),
            ]
        ]);
    }
}