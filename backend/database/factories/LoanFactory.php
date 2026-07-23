<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Loans\Models\Loan;
use App\Library\Domains\Users\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    protected $model = Loan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $loanedAt = CarbonImmutable::today()->subDays(fake()->numberBetween(0, 10));

        return [
            'user_id' => User::factory()->member(),
            'book_id' => Book::factory(),
            'loaned_at' => $loanedAt,
            'due_date' => $loanedAt->addDays(config('library.loans.default_period_days')),
            'returned_at' => null,
        ];
    }

    public function overdue(int $daysOverdue = 3): static
    {
        return $this->state(function (array $attributes) use ($daysOverdue) {
            $dueDate = CarbonImmutable::today()->subDays($daysOverdue);

            return [
                'loaned_at' => $dueDate->subDays(config('library.loans.default_period_days')),
                'due_date' => $dueDate,
                'returned_at' => null,
            ];
        });
    }

    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'returned_at' => CarbonImmutable::now(),
        ]);
    }
}
