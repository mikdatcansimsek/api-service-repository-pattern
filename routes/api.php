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
    Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');

    // Authenticated routes
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('/user', [AuthController::class, 'user'])->name('api.auth.user');
    });
});

// Public API routes
Route::middleware(['cors', 'api.rate.limit'])->group(function () {

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('api.products.index');
        Route::post('/', [ProductController::class, 'store'])->name('api.products.store');
        Route::get('/{id}', [ProductController::class, 'show'])->name('api.products.show');
        Route::put('/{id}', [ProductController::class, 'update'])->name('api.products.update');
        Route::patch('/{id}', [ProductController::class, 'update'])->name('api.products.patch');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('api.products.destroy');

        Route::get('/sku/{sku}', [ProductController::class, 'findBySku'])->name('api.products.findBySku');
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('api.categories.index');
        Route::post('/', [CategoryController::class, 'store'])->name('api.categories.store');
        Route::get('/{id}', [CategoryController::class, 'show'])->name('api.categories.show');
        Route::put('/{id}', [CategoryController::class, 'update'])->name('api.categories.update');
        Route::patch('/{id}', [CategoryController::class, 'update'])->name('api.categories.patch');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('api.categories.destroy');

        Route::get('/slug/{slug}', [CategoryController::class, 'findBySlug'])->name('api.categories.findBySlug');

        // Resource'larda kullanılan route'lar
        Route::get('/{category}/products', [CategoryController::class, 'products'])->name('api.categories.products');
        Route::get('/{category}/posts', [CategoryController::class, 'posts'])->name('api.categories.posts');
    });

    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index'])->name('api.posts.index');
        Route::post('/', [PostController::class, 'store'])->name('api.posts.store');
        Route::get('/{id}', [PostController::class, 'show'])->name('api.posts.show');
        Route::put('/{id}', [PostController::class, 'update'])->name('api.posts.update');
        Route::patch('/{id}', [PostController::class, 'update'])->name('api.posts.patch');
        Route::delete('/{id}', [PostController::class, 'destroy'])->name('api.posts.destroy');

        Route::get('/slug/{slug}', [PostController::class, 'findBySlug'])->name('api.posts.findBySlug');
        Route::post('/{id}/publish', [PostController::class, 'publish'])->name('api.posts.publish');
        Route::post('/{id}/unpublish', [PostController::class, 'unpublish'])->name('api.posts.unpublish');

        // Resource'larda kullanılan route'lar
        Route::get('/{post}/comments', [PostController::class, 'comments'])->name('api.posts.comments');
    });

    // Users routes (Resource'larda kullanılıyor)
    Route::prefix('users')->group(function () {
        Route::get('/{id}', function($id) {
            return response()->json(['message' => 'User endpoint not implemented yet']);
        })->name('api.users.show');

        Route::get('/{user}/posts', function($user) {
            return response()->json(['message' => 'User posts endpoint not implemented yet']);
        })->name('api.users.posts');

        Route::get('/{user}/products', function($user) {
            return response()->json(['message' => 'User products endpoint not implemented yet']);
        })->name('api.users.products');
    });
});

// Protected API routes (authentication required)
Route::middleware(['auth:api', 'cors', 'api.rate.limit'])->group(function () {
    // Admin-only routes buraya eklenebilir
    // Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});
