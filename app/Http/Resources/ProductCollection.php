<?php

// app/Http/Resources/ProductCollection.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\CustomCollection;

class ProductCollection extends CustomCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($product) {
                return new ProductResource($product);
            }),
        ];
    }

    /**
     * Get available filters specific to products
     */
    protected function getAvailableFilters(): array
    {
        return [
            'status' => [
                'active' => 'Aktif Ürünler',
                'inactive' => 'Pasif Ürünler',
            ],
            'availability' => [
                'in_stock' => 'Stokta Olanlar',
                'out_of_stock' => 'Stokta Olmayanlar',
                'low_stock' => 'Az Stokta Olanlar (< 10)',
            ],
            'category' => 'Kategoriye Göre',
            'price_range' => [
                '0-100' => '0-100 TL',
                '100-500' => '100-500 TL',
                '500-1000' => '500-1000 TL',
                '1000+' => '1000+ TL',
            ],
            'date_range' => [
                'today' => 'Bugün Eklenenler',
                'week' => 'Bu Hafta',
                'month' => 'Bu Ay',
                'year' => 'Bu Yıl',
            ],
        ];
    }

    /**
     * Get available sorting options for products
     */
    protected function getAvailableSorting(): array
    {
        return [
            'name' => 'Ürün Adı',
            'price' => 'Fiyat',
            'quantity' => 'Stok Miktarı',
            'created_at' => 'Eklenme Tarihi',
            'updated_at' => 'Güncelleme Tarihi',
            'category_name' => 'Kategori Adı',
            'sku' => 'Ürün Kodu',
        ];
    }

    /**
     * Calculate product-specific statistics
     */
    public function calculateProductStatistics(): self
    {
        if ($this->collection->isEmpty()) {
            return $this;
        }

        $products = $this->collection->pluck('resource');

        $stats = [
            'products' => [
                'total_count' => $products->count(),
                'active_count' => $products->where('is_active', true)->count(),
                'inactive_count' => $products->where('is_active', false)->count(),
            ],
            'stock' => [
                'in_stock_count' => $products->where('quantity', '>', 0)->count(),
                'out_of_stock_count' => $products->where('quantity', '<=', 0)->count(),
                'low_stock_count' => $products->where('quantity', '>', 0)->where('quantity', '<', 10)->count(),
                'total_quantity' => $products->sum('quantity'),
            ],
            'pricing' => [
                'min_price' => $products->min('price'),
                'max_price' => $products->max('price'),
                'avg_price' => round($products->avg('price'), 2),
                'total_value' => round($products->sum(function ($product) {
                    return $product->price * $product->quantity;
                }), 2),
            ],
            'categories' => [
                'unique_categories' => $products->unique('category_id')->count(),
                'most_popular_category' => $products->groupBy('category_id')
                    ->map->count()
                    ->sortDesc()
                    ->keys()
                    ->first(),
            ],
        ];

        $this->statistics = array_merge($this->statistics, $stats);
        return $this;
    }

    /**
     * Add product-specific bulk operations
     */
    public function withProductBulkOperations(): self
    {
        $operations = [
            'update_price' => 'Fiyat Güncelle',
            'update_stock' => 'Stok Güncelle',
            'change_category' => 'Kategori Değiştir',
            'activate' => 'Aktif Yap',
            'deactivate' => 'Pasif Yap',
            'duplicate' => 'Kopyala',
            'export_excel' => 'Excel\'e Aktar',
            'export_csv' => 'CSV\'ye Aktar',
        ];

        return $this->withBulkOperations($operations);
    }

    /**
     * Get collection type name
     */
    protected function getCollectionType(): string
    {
        return 'products';
    }
}

// app/Http/Resources/CategoryCollection.php
namespace App\Http\Resources;

use Illuminate\Http\Request;

class CategoryCollection extends CustomCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($category) {
                return new CategoryResource($category);
            }),
        ];
    }

    /**
     * Get available filters specific to categories
     */
    protected function getAvailableFilters(): array
    {
        return [
            'status' => [
                'active' => 'Aktif Kategoriler',
                'inactive' => 'Pasif Kategoriler',
            ],
            'level' => [
                '0' => 'Ana Kategoriler',
                '1' => 'Alt Kategoriler',
                '2' => 'Alt Alt Kategoriler',
            ],
            'has_products' => [
                'with_products' => 'Ürünü Olanlar',
                'without_products' => 'Ürünü Olmayanlar',
            ],
        ];
    }

    /**
     * Get available sorting options for categories
     */
    protected function getAvailableSorting(): array
    {
        return [
            'name' => 'Kategori Adı',
            'products_count' => 'Ürün Sayısı',
            'level' => 'Seviye',
            'sort_order' => 'Sıralama',
            'created_at' => 'Eklenme Tarihi',
        ];
    }

    /**
     * Calculate category-specific statistics
     */
    public function calculateCategoryStatistics(): self
    {
        if ($this->collection->isEmpty()) {
            return $this;
        }

        $categories = $this->collection->pluck('resource');

        $stats = [
            'categories' => [
                'total_count' => $categories->count(),
                'active_count' => $categories->where('is_active', true)->count(),
                'inactive_count' => $categories->where('is_active', false)->count(),
            ],
            'hierarchy' => [
                'main_categories' => $categories->whereNull('parent_id')->count(),
                'sub_categories' => $categories->whereNotNull('parent_id')->count(),
                'max_level' => $categories->max('level') ?? 0,
            ],
            'products' => [
                'total_products' => $categories->sum('products_count'),
                'avg_products_per_category' => round($categories->avg('products_count'), 2),
                'categories_with_products' => $categories->where('products_count', '>', 0)->count(),
            ],
        ];

        $this->statistics = array_merge($this->statistics, $stats);
        return $this;
    }

    /**
     * Get collection type name
     */
    protected function getCollectionType(): string
    {
        return 'categories';
    }
}

// app/Http/Resources/PostCollection.php
namespace App\Http\Resources;

use Illuminate\Http\Request;

class PostCollection extends CustomCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($post) {
                return new PostResource($post);
            }),
        ];
    }

    /**
     * Get available filters specific to posts
     */
    protected function getAvailableFilters(): array
    {
        return [
            'status' => [
                'published' => 'Yayınlanan',
                'draft' => 'Taslak',
                'scheduled' => 'Zamanlanmış',
            ],
            'category' => 'Kategoriye Göre',
            'author' => 'Yazara Göre',
            'date_range' => [
                'today' => 'Bugün',
                'week' => 'Bu Hafta',
                'month' => 'Bu Ay',
                'year' => 'Bu Yıl',
            ],
            'featured' => [
                'featured' => 'Öne Çıkanlar',
                'regular' => 'Normal Postlar',
            ],
        ];
    }

    /**
     * Get available sorting options for posts
     */
    protected function getAvailableSorting(): array
    {
        return [
            'title' => 'Başlık',
            'published_at' => 'Yayın Tarihi',
            'created_at' => 'Oluşturma Tarihi',
            'view_count' => 'Görüntülenme',
            'like_count' => 'Beğeni Sayısı',
            'comment_count' => 'Yorum Sayısı',
            'author_name' => 'Yazar Adı',
        ];
    }

    /**
     * Calculate post-specific statistics
     */
    public function calculatePostStatistics(): self
    {
        if ($this->collection->isEmpty()) {
            return $this;
        }

        $posts = $this->collection->pluck('resource');

        $stats = [
            'posts' => [
                'total_count' => $posts->count(),
                'published_count' => $posts->where('is_published', true)->count(),
                'draft_count' => $posts->where('is_published', false)->count(),
            ],
            'engagement' => [
                'total_views' => $posts->sum('view_count'),
                'total_likes' => $posts->sum('likes_count'),
                'total_comments' => $posts->sum('comments_count'),
                'avg_views_per_post' => round($posts->avg('view_count'), 2),
            ],
            'content' => [
                'avg_reading_time' => round($posts->avg('reading_time_minutes'), 2),
                'total_reading_time' => $posts->sum('reading_time_minutes'),
                'posts_with_images' => $posts->whereNotNull('featured_image')->count(),
            ],
            'authors' => [
                'unique_authors' => $posts->unique('user_id')->count(),
                'most_productive_author' => $posts->groupBy('user_id')
                    ->map->count()
                    ->sortDesc()
                    ->keys()
                    ->first(),
            ],
        ];

        $this->statistics = array_merge($this->statistics, $stats);
        return $this;
    }

    /**
     * Get collection type name
     */
    protected function getCollectionType(): string
    {
        return 'posts';
    }
}
