<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ProductResource extends CustomResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            ...$this->getResourceIdentifier(),
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'sku' => $this->resource->sku,
            'quantity' => $this->resource->quantity,
            'is_active' => $this->resource->is_active,

            'price' => $this->formatCurrency($this->resource->price),

            'stock_status' => [
                'quantity' => $this->resource->quantity,
                'is_available' => $this->resource->quantity > 0 && $this->resource->is_active,
                'status_text' => $this->resource->quantity > 0 && $this->resource->is_active ? 'Stokta' : 'Stokta Yok',
            ],

            'category' => $this->loadRelationship('category', CategoryResource::class),            'tags' => $this->resource->tags,
            
            'user_data' => $this->whenAuth([
                'is_favorited' => false, // Buraya favorileme logic'i eklenebilir
                'user_rating' => null,   // Buraya rating logic'i eklenebilir
            ]),

            ...$this->getTimestamps(),
        ];
    }
}
