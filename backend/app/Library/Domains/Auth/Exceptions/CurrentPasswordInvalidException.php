<?php

declare(strict_types=1);

namespace App\Library\Domains\Auth\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * FR-AUTH-7: proving ownership of the current password is what stops a stolen
 * token from becoming a permanent account takeover.
 */
final class CurrentPasswordInvalidException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The current password is incorrect.');
    }

    public function errorCode(): string
    {
        return 'CURRENT_PASSWORD_INVALID';
    }

    public function httpStatus(): int
    {
        return 422;
    }
}
