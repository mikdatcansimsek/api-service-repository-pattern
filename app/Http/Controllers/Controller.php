<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="API Service Repository Pattern",
 *     version="1.0.0",
 *     description="Laravel API with Repository Pattern & Service Layer - Laravel Passport Auth",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Development Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Laravel Passport Personal Access Token"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Authentication Operations"
 * )
 *
 * @OA\Tag(
 *     name="Products",
 *     description="Product Management"
 * )
 *
 * @OA\Tag(
 *     name="Categories",
 *     description="Category Management"
 * )
 *
 * @OA\Tag(
 *     name="Posts",
 *     description="Post Management"
 * )
 */
abstract class Controller
{
    //
}
