<?php

namespace App\Http\Controllers\Api\Schemas;

/**
 * @OA\Schema(
 *     schema="Post",
 *     type="object",
 *     title="Post",
 *     required={"id","title","content","user_id","category_id"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="My First Post"),
 *     @OA\Property(property="slug", type="string", example="my-first-post"),
 *     @OA\Property(property="content", type="string", example="This is the content of my first post"),
 *     @OA\Property(property="excerpt", type="string", example="Short description of the post"),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="is_published", type="boolean", example=true),
 *     @OA\Property(property="published_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
 * )
 */
class PostSchema
{
    // Schema sadece annotation için
}
