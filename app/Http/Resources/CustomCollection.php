<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Custom Base Collection Class
 *
 * Provides:
 * - Enhanced pagination meta data
 * - Collection statistics
 * - Filter and sort information
 * - Performance metrics
 * - Consistent collection structure
 */
abstract class CustomCollection extends ResourceCollection
{
    /**
     * Additional meta information for the collection
     */
    protected array $additionalMeta = [];

    /**
     * Applied filters information
     */
    protected array $appliedFilters = [];

    /**
     * Applied sorting information
     */
    protected array $appliedSorting = [];

    /**
     * Collection statistics
     */
    protected array $statistics = [];

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    /**
     * Get additional data for the collection response
     */
    public function with(Request $request): array
    {
        $meta = [
            'success' => true,
            'timestamp' => now()->toISOString(),
            'version' => 'v1',
            'collection_type' => $this->getCollectionType(),
            'total_items' => $this->count(),
        ];

        // Add pagination meta if paginated
        if ($this->isPaginated()) {
            $meta['pagination'] = $this->getPaginationMeta();
        }

        // Add statistics if available
        if (!empty($this->statistics)) {
            $meta['statistics'] = $this->statistics;
        }

        // Add filter information
        if (!empty($this->appliedFilters)) {
            $meta['filters'] = [
                'applied' => $this->appliedFilters,
                'available' => $this->getAvailableFilters(),
            ];
        }

        // Add sorting information
        if (!empty($this->appliedSorting)) {
            $meta['sorting'] = [
                'applied' => $this->appliedSorting,
                'available' => $this->getAvailableSorting(),
            ];
        }

        // Add performance metrics (sadece debug modunda)
        if (config('app.debug')) {
            $meta['performance'] = $this->getPerformanceMetrics();
        }

        // Merge additional meta
        $meta = array_merge($meta, $this->additionalMeta);

        $response = ['meta' => $meta];

        // Add navigation links if paginated
        if ($this->isPaginated()) {
            $response['links'] = $this->getNavigationLinks();
        }

        return $response;
    }

    /**
     * Get collection type name
     */
    protected function getCollectionType(): string
    {
        return class_basename(static::class);
    }

    /**
     * Check if collection is paginated
     */
    protected function isPaginated(): bool
    {
        return $this->resource instanceof LengthAwarePaginator;
    }

    /**
     * Get pagination meta information
     */
    protected function getPaginationMeta(): array
    {
        if (!$this->isPaginated()) {
            return [];
        }

        $paginator = $this->resource;

        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'count' => $paginator->count(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_more_pages' => $paginator->hasMorePages(),
            'has_previous_page' => $paginator->currentPage() > 1,
            'path' => $paginator->path(),
        ];
    }

    /**
     * Get navigation links for pagination
     */
    protected function getNavigationLinks(): array
    {
        if (!$this->isPaginated()) {
            return [];
        }

        $paginator = $this->resource;

        return [
            'first' => $paginator->url(1),
            'last' => $paginator->url($paginator->lastPage()),
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
            'self' => $paginator->url($paginator->currentPage()),
        ];
    }

    /**
     * Get available filters for this collection
     */
    protected function getAvailableFilters(): array
    {
        return [
            'status' => ['active', 'inactive'],
            'sort' => ['name', 'created_at', 'updated_at'],
            'order' => ['asc', 'desc'],
        ];
    }

    /**
     * Get available sorting options
     */
    protected function getAvailableSorting(): array
    {
        return [
            'name' => 'Name',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
            'price' => 'Price',
        ];
    }

    /**
     * Get performance metrics
     */
    protected function getPerformanceMetrics(): array
    {
        return [
            'execution_time' => round((microtime(true) - LARAVEL_START) * 1000, 2) . 'ms',
            'memory_usage' => $this->formatBytes(memory_get_peak_usage(true)),
            'query_count' => \DB::getQueryLog() ? count(\DB::getQueryLog()) : 'N/A',
        ];
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Add custom meta information
     */
    public function additional(array $meta): self
    {
        $this->additionalMeta = array_merge($this->additionalMeta, $meta);
        return $this;
    }

    /**
     * Set applied filters
     */
    public function withFilters(array $filters): self
    {
        $this->appliedFilters = $filters;
        return $this;
    }

    /**
     * Set applied sorting
     */
    public function withSorting(array $sorting): self
    {
        $this->appliedSorting = $sorting;
        return $this;
    }

    /**
     * Set collection statistics
     */
    public function withStatistics(array $statistics): self
    {
        $this->statistics = $statistics;
        return $this;
    }

    /**
     * Calculate collection statistics
     */
    public function calculateStatistics(?string $field = null): self
    {
        if ($this->collection->isEmpty()) {
            return $this;
        }

        $stats = [
            'total_count' => $this->collection->count(),
        ];

        // Calculate numeric field statistics
        if ($field && $this->collection->first()->resource->$field ?? false) {
            $values = $this->collection->pluck("resource.$field")->filter()->values();

            if ($values->isNotEmpty()) {
                $stats[$field . '_statistics'] = [
                    'min' => $values->min(),
                    'max' => $values->max(),
                    'avg' => round($values->avg(), 2),
                    'sum' => $values->sum(),
                ];
            }
        }

        $this->statistics = array_merge($this->statistics, $stats);
        return $this;
    }

    /**
     * Add search meta information
     */
    public function withSearch(string $query, int $totalResults): self
    {
        $this->additionalMeta['search'] = [
            'query' => $query,
            'total_results' => $totalResults,
            'has_results' => $totalResults > 0,
            'results_text' => $totalResults . ' sonuç bulundu',
        ];

        return $this;
    }

    /**
     * Add bulk operations information
     */
    public function withBulkOperations(array $operations = []): self
    {
        $defaultOperations = [
            'delete' => 'Seçilenleri Sil',
            'export' => 'Seçilenleri Dışa Aktar',
            'update_status' => 'Durumu Güncelle',
        ];

        $this->additionalMeta['bulk_operations'] = array_merge($defaultOperations, $operations);
        return $this;
    }

    /**
     * Create a new collection instance
     */
    public static function collection($resource): static
    {
        return new static($resource);
    }
}
