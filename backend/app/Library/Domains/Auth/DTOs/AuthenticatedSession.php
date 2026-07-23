<?php

declare(strict_types=1);

namespace App\Library\Domains\Auth\DTOs;

use App\Library\Domains\Users\Models\User;

/**
 * What register and login return: the bearer token plus the account it belongs
 * to (API specification 2).
 */
final readonly class AuthenticatedSession
{
    public function __construct(
        public User $user,
        public string $token,
        public string $tokenType = 'Bearer',
    ) {}
}
