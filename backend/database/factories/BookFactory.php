<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Library\Domains\Books\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalCopies = fake()->numberBetween(1, 8);

        return [
            'title' => rtrim(fake()->sentence(3), '.'),
            'isbn' => $this->isbn13(),
            'description' => fake()->paragraph(),
            'publisher' => fake()->company(),
            'publication_year' => fake()->numberBetween(1950, (int) date('Y')),
            'cover_url' => fake()->optional()->imageUrl(),
            'total_copies' => $totalCopies,
            'available_copies' => $totalCopies,
        ];
    }

    /**
     * Every copy is checked out — the fixture for BR-LOAN-1 (`LOAN_NO_COPIES`).
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => ['available_copies' => 0]);
    }

    public function withCopies(int $total, ?int $available = null): static
    {
        return $this->state(fn (array $attributes) => [
            'total_copies' => $total,
            'available_copies' => $available ?? $total,
        ]);
    }

    /**
     * A checksum-valid ISBN-13, since the domain rejects anything else (FR-VAL-3).
     */
    private function isbn13(): string
    {
        $digits = '978'.str_pad((string) fake()->unique()->numberBetween(0, 999999999), 9, '0', STR_PAD_LEFT);

        $sum = 0;
        foreach (str_split($digits) as $index => $digit) {
            $sum += (int) $digit * ($index % 2 === 0 ? 1 : 3);
        }

        return $digits.((10 - $sum % 10) % 10);
    }
}
