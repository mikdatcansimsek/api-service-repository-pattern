<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test-passport', function () {
    return response()->json([
        'message' => 'Laravel Passport başarıyla çalışıyor!',
        'timestamp' => now(),
        'status' => 'success'
    ]);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

// Auth routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Authenticated routes
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

// Public API routes
Route::middleware(['cors', 'api.rate.limit'])->group(function () {

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::patch('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);

        Route::get('/sku/{sku}', [ProductController::class, 'findBySku']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::patch('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);

        Route::get('/slug/{slug}', [CategoryController::class, 'findBySlug']);
    });

    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::post('/', [PostController::class, 'store']);
        Route::get('/{id}', [PostController::class, 'show']);
        Route::put('/{id}', [PostController::class, 'update']);
        Route::patch('/{id}', [PostController::class, 'update']);
        Route::delete('/{id}', [PostController::class, 'destroy']);

        Route::get('/slug/{slug}', [PostController::class, 'findBySlug']);
        Route::post('/{id}/publish', [PostController::class, 'publish']);
        Route::post('/{id}/unpublish', [PostController::class, 'unpublish']);
    });
});

// Protected API routes (authentication required)
Route::middleware(['auth:api', 'cors', 'api.rate.limit'])->group(function () {
    // Admin-only routes buraya eklenebilir
    // Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});
