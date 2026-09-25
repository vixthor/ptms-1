<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'title' => fake()->sentence(3),
            'hero_title' => fake()->sentence(4),
            'hero_subtitle' => fake()->sentence(8),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'meta_description' => fake()->sentence(12),
            'published' => true,
        ];
    }
}
