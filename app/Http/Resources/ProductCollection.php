<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

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
     * PERFORMANCE OPTIMIZATION:
     * Ağır statistics hesaplamaları kaldırıldı - Service layer'a taşındı
     * Artık sadece Service'den pre-calculated statistics alıyor
     */

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