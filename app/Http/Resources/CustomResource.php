<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth; // ← BU SATIRI EKLE

/**
 * Custom Base Resource Class
 *
 * Mevcut Laravel JsonResource'ının geliştirilmiş hali
 * Tutarlı API response'lar için özelleştirmeler içerir
 */
class CustomResource extends JsonResource
{
    /**
     * Response'a meta data ekle
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'success' => true,
                'timestamp' => now()->toISOString(),
                'version' => 'v1',
            ]
        ];
    }

    /**
     * Resource identifier (ID, type, uuid) al
     */
    protected function getResourceIdentifier(): array
    {
        return [
            'id' => $this->resource->id,
            'type' => class_basename($this->resource),
        ];
    }

    /**
     * Para birimi formatla
     */
    protected function formatCurrency($amount, string $currency = 'TRY'): array
    {
        return [
            'amount' => (float) $amount,
            'currency' => $currency,
            'formatted' => number_format($amount, 2) . ' ' . $currency,
        ];
    }

    /**
     * Tarihleri tutarlı formatta göster
     */
    protected function formatDate($date): array|null
    {
        if (!$date) {
            return null;
        }

        $carbonDate = \Carbon\Carbon::parse($date);

        return [
            'date' => $carbonDate->format('Y-m-d H:i:s'),
            'iso' => $carbonDate->toISOString(),
            'human' => $carbonDate->diffForHumans(),
        ];
    }

    /**
     * Standart timestamp'leri al
     */
    protected function getTimestamps(): array
    {
        $timestamps = [];

        if (isset($this->resource->created_at)) {
            $timestamps['created_at'] = $this->formatDate($this->resource->created_at);
        }

        if (isset($this->resource->updated_at)) {
            $timestamps['updated_at'] = $this->formatDate($this->resource->updated_at);
        }

        return $timestamps;
    }

    /**
     * Kullanıcı giriş yapmışsa göster
     */
    protected function whenAuth($value = null, $default = null)
    {
        if ($this->isAuthenticated()) {
            return $value ?? $this->resource;
        }

        return $default;
    }

    /**
     * Sadece kaynak sahibi görebilir
     */
    protected function whenOwner($value = null, $default = null)
    {
        if ($this->isAuthenticated() && isset($this->resource->user_id) && $this->getAuthUserId() === $this->resource->user_id) {
            return $value ?? $this->resource;
        }

        return $default;
    }

    /**
     * Kullanıcı giriş yapmış mı kontrol et
     */
    protected function isAuthenticated(): bool
    {
        return Auth::check();
    }

    /**
     * Giriş yapmış kullanıcının ID'sini al
     */
    protected function getAuthUserId(): ?int
    {
        return Auth::id();
    }

    /**
     * Giriş yapmış kullanıcıyı al
     */
    protected function getAuthUser()
    {
        return Auth::user();
    }

    /**
     * Relationship'i güvenli şekilde yükle
     */
    protected function loadRelationship($relationship, $resourceClass)
    {
        return $this->whenLoaded($relationship, function () use ($resourceClass, $relationship) {
            $related = $this->resource->getRelation($relationship);

            if (is_null($related)) {
                return null;
            }

            // Eğer collection ise
            if ($related instanceof \Illuminate\Support\Collection) {
                return $resourceClass::collection($related);
            }

            // Tek model ise
            return new $resourceClass($related);
        });
    }
}
