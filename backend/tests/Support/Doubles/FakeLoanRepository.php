<?php

declare(strict_types=1);

namespace Tests\Support\Doubles;

use App\Library\Domains\Loans\Contracts\LoanRepositoryInterface;

final class FakeLoanRepository implements LoanRepositoryInterface
{
    /**
     * @param  array<int, int>  $activeLoansByUser
     */
    public function __construct(private array $activeLoansByUser = []) {}

    public function countActiveForUser(int $userId): int
    {
        return $this->activeLoansByUser[$userId] ?? 0;
    }
}
