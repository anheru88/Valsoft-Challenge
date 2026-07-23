<?php

declare(strict_types=1);

namespace App\Library\Domains\Auth\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * Deliberately generic: an unknown email and a wrong password are
 * indistinguishable to the caller, so the endpoint cannot be used to enumerate
 * accounts (API specification 2).
 */
final class InvalidCredentialsException extends DomainException
{
    public function __construct()
    {
        parent::__construct('These credentials do not match our records.');
    }

    public function errorCode(): string
    {
        return 'INVALID_CREDENTIALS';
    }

    public function httpStatus(): int
    {
        return 422;
    }
}
