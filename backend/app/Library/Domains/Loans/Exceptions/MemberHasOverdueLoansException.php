<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * BR-LOAN-3: overdue items must come back before new ones go out.
 */
final class MemberHasOverdueLoansException extends DomainException
{
    private function __construct(private readonly int $overdueLoans)
    {
        parent::__construct('This member has overdue loans and cannot borrow until they are returned.');
    }

    public static function withCount(int $overdueLoans): self
    {
        return new self($overdueLoans);
    }

    public function errorCode(): string
    {
        return 'LOAN_MEMBER_OVERDUE';
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return ['overdue_loans' => $this->overdueLoans];
    }
}
