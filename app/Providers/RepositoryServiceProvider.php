<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;


// Repository Interfaces
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;

// Repository Implementations
use App\Repositories\ProductRepository;
use App\Repositories\PostRepository;
use App\Repositories\CategoryRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
