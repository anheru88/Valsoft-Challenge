<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Contracts;

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
}
