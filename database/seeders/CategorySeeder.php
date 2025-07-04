<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;

use Illuminate\Database\Seeder;
use Illuminate\Validation\Rules\Can;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic products'],
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Fashion and clothing'],
            ['name' => 'Books', 'slug' => 'books', 'description' => 'Books and literature'],
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Technology posts'],
            ['name' => 'Lifestyle', 'slug' => 'lifestyle', 'description' => 'Lifestyle articles'],
        ];

        foreach ($categories as $category){
            Category::create($category);

        }

        Category::factory(5)->create();
    }
}
