<?php

declare(strict_types=1);

namespace App\Library\Domains\Auth\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * FR-USER-6: a deactivated account cannot authenticate. Distinct from invalid
 * credentials because the caller proved they own the account — telling them it
 * is disabled leaks nothing and saves a support ticket.
 */
final class UserInactiveException extends DomainException
{
    public function __construct()
    {
        parent::__construct('This account has been deactivated. Contact a librarian.');
    }

    public function errorCode(): string
    {
        return 'USER_INACTIVE';
    }

    public function httpStatus(): int
    {
        return 403;
    }
}
