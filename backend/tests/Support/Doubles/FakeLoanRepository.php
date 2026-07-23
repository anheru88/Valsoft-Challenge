<?php

declare(strict_types=1);

namespace Tests\Support\Doubles;

use App\Library\Domains\Loans\Contracts\LoanRepositoryInterface;
use App\Library\Domains\Loans\DTOs\LoanFilters;
use App\Library\Domains\Loans\Models\Loan;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use RuntimeException;

/**
 * Answers the circulation questions other domains' rules ask, in memory. The
 * write side is covered by feature tests against the real database, so it is
 * left unimplemented rather than faked into something that could drift.
 */
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

    public function countOverdueForUser(int $userId, CarbonImmutable $now): int
    {
        return 0;
    }

    public function hasActiveLoanForBook(int $userId, int $bookId): bool
    {
        return false;
    }

    public function paginate(LoanFilters $filters): LengthAwarePaginator
    {
        throw new RuntimeException('Listing is covered by feature tests.');
    }

    public function findById(int $id): ?Loan
    {
        throw new RuntimeException('Reading is covered by feature tests.');
    }

    public function create(int $userId, int $bookId, CarbonImmutable $loanedAt, CarbonImmutable $dueDate): Loan
    {
        throw new RuntimeException('Check-out is covered by feature tests.');
    }

    public function markReturned(Loan $loan, CarbonImmutable $returnedAt): Loan
    {
        throw new RuntimeException('Check-in is covered by feature tests.');
    }
}
