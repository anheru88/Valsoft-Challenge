<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * BR-LOAN-2.
 */
final class LoanLimitReachedException extends DomainException
{
    private function __construct(
        private readonly int $limit,
        private readonly int $activeLoans,
    ) {
        parent::__construct("This member already has {$activeLoans} active loans (limit {$limit}).");
    }

    public static function make(int $limit, int $activeLoans): self
    {
        return new self($limit, $activeLoans);
    }

    public function errorCode(): string
    {
        return 'LOAN_LIMIT_REACHED';
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return ['limit' => $this->limit, 'active_loans' => $this->activeLoans];
    }
}
