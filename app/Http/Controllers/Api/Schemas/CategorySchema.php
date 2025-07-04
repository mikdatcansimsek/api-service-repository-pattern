<?php

namespace App\Http\Controllers\Api\Schemas;

/**
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     title="Category",
 *     required={"id","name","slug"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Electronics"),
 *     @OA\Property(property="slug", type="string", example="electronics"),
 *     @OA\Property(property="description", type="string", example="Electronic products"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
 * )
 */
class CategorySchema
{
    // Schema sadece annotation için
}
