<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * BR-BOOK-4: a title with copies in members' hands cannot be removed from the
 * catalogue — the open loans would point at nothing.
 */
final class BookHasActiveLoansException extends DomainException
{
    private function __construct(private readonly int $activeLoans)
    {
        parent::__construct('This book has active loans and cannot be deleted.');
    }

    public static function withCount(int $activeLoans): self
    {
        return new self($activeLoans);
    }

    public function errorCode(): string
    {
        return 'BOOK_HAS_ACTIVE_LOANS';
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return ['active_loans' => $this->activeLoans];
    }
}
