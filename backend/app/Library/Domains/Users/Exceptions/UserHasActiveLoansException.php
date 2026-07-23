<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * BR-USER-1: a member holding books cannot be removed — the copies would be
 * unaccounted for.
 */
final class UserHasActiveLoansException extends DomainException
{
    private function __construct(private readonly int $activeLoans)
    {
        parent::__construct('This user still has active loans and cannot be deleted.');
    }

    public static function withCount(int $activeLoans): self
    {
        return new self($activeLoans);
    }

    public function errorCode(): string
    {
        return 'USER_HAS_ACTIVE_LOANS';
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return ['active_loans' => $this->activeLoans];
    }
}
