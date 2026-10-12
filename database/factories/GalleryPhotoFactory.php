<?php

namespace Database\Factories;

use App\Models\GalleryPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryPhoto>
 */
class GalleryPhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'image' => 'sikhi/sikhi1.jpeg',
            'caption' => fake()->sentence(4),
            'category' => fake()->randomElement(['Campus', 'Events', 'Classroom', 'Sports']),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
