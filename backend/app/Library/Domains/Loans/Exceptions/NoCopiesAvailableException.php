<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * BR-LOAN-1. Also the loser of a race for the last copy: the row lock lets
 * exactly one check-out through and this is what the other one gets.
 */
final class NoCopiesAvailableException extends DomainException
{
    public function __construct()
    {
        parent::__construct('No copies of this book are currently available.');
    }

    public function errorCode(): string
    {
        return 'LOAN_NO_COPIES';
    }
}
