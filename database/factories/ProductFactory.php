<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $name = fake()->words(3, true);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'quantity' => fake()->numberBetween(0, 100),
            'sku' => fake()->unique()->regexify('[A-Z]{3}[0-9]{6}'),
            'category_id' => Category::factory(),
            'is_active' => fake()->boolean(85),
        ];
    }
}
