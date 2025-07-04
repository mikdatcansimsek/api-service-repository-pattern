<?php

namespace App\Http\Controllers\Api\Schemas;

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     required={"id","name","price","sku","category_id"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="iPhone 15"),
 *     @OA\Property(property="slug", type="string", example="iphone-15"),
 *     @OA\Property(property="description", type="string", example="Latest iPhone model"),
 *     @OA\Property(property="price", type="number", format="float", example=999.99),
 *     @OA\Property(property="quantity", type="integer", example=50),
 *     @OA\Property(property="sku", type="string", example="IP15001"),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
 * )
 */
class ProductSchema
{
    // Schema sadece annotation için - kod yok
}
