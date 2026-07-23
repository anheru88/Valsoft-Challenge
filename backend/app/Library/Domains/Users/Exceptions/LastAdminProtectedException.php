<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * BR-USER-3: the system must always retain at least one active administrator.
 * Deleting, demoting or deactivating the last one would lock everybody out of
 * user management, so the domain refuses.
 */
final class LastAdminProtectedException extends DomainException
{
    public static function forDeletion(): self
    {
        return new self('The last active administrator cannot be deleted.');
    }

    public static function forRoleChange(): self
    {
        return new self('The last active administrator cannot be demoted.');
    }

    public static function forDeactivation(): self
    {
        return new self('The last active administrator cannot be deactivated.');
    }

    public function errorCode(): string
    {
        return 'LAST_ADMIN_PROTECTED';
    }
}
