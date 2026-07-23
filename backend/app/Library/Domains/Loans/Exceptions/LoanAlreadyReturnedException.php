<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * FR-LOAN-2 / BR-LOAN-8: a closed loan is immutable, so a second check-in is
 * refused rather than silently incrementing the counter twice.
 */
final class LoanAlreadyReturnedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('This loan has already been returned.');
    }

    public function errorCode(): string
    {
        return 'LOAN_ALREADY_RETURNED';
    }
}
