<?php

declare(strict_types=1);

namespace Tests\Support\Doubles;

use App\Library\Domains\Loans\Contracts\LoanRepositoryInterface;

final class FakeLoanRepository implements LoanRepositoryInterface
{
    /**
     * @param  array<int, int>  $activeLoansByUser
     * @param  array<int, int>  $activeLoansByBook
     */
    public function __construct(
        private array $activeLoansByUser = [],
        private array $activeLoansByBook = [],
    ) {}

    public function countActiveForUser(int $userId): int
    {
        return $this->activeLoansByUser[$userId] ?? 0;
    }

    public function countActiveForBook(int $bookId): int
    {
        return $this->activeLoansByBook[$bookId] ?? 0;
    }
}
