<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Library\Domains\Authors\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    protected $model = Author::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'bio' => fake()->optional()->paragraph(),
            'birth_year' => fake()->numberBetween(1850, 1995),
        ];
    }
}
