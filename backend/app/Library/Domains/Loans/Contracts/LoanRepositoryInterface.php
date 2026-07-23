<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Contracts;

use App\Library\Domains\Loans\DTOs\LoanFilters;
use App\Library\Domains\Loans\Models\Loan;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * The Loans domain's public seam. Other domains ask their circulation questions
 * through this interface and never reach into the loans tables themselves,
 * which is the boundary that keeps the monolith modular (RFC 4).
 */
interface LoanRepositoryInterface
{
    /**
     * Open loans held by a member — the guard for BR-USER-1 and the limit in
     * BR-LOAN-2.
     */
    public function countActiveForUser(int $userId): int;

    /**
     * Open loans against a title — the guard for BR-BOOK-4.
     */
    public function countActiveForBook(int $bookId): int;

    /**
     * BR-LOAN-3: open loans already past their due date.
     */
    public function countOverdueForUser(int $userId, CarbonImmutable $now): int;

    /**
     * BR-LOAN-4.
     */
    public function hasActiveLoanForBook(int $userId, int $bookId): bool;

    /**
     * @return LengthAwarePaginator<int, Loan>
     */
    public function paginate(LoanFilters $filters): LengthAwarePaginator;

    public function findById(int $id): ?Loan;

    public function create(int $userId, int $bookId, CarbonImmutable $loanedAt, CarbonImmutable $dueDate): Loan;

    public function markReturned(Loan $loan, CarbonImmutable $returnedAt): Loan;
}
