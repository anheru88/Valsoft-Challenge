<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * BR-LOAN-4 / FR-LOAN-6: one member, one copy of a given title at a time.
 */
final class DuplicateActiveLoanException extends DomainException
{
    public function __construct()
    {
        parent::__construct('This member already has an active loan for this book.');
    }

    public function errorCode(): string
    {
        return 'LOAN_DUPLICATE_TITLE';
    }
}
