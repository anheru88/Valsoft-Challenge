<?php

declare(strict_types=1);

namespace App\Library\Domains\Auth\Actions;

use App\Library\Domains\Auth\Contracts\TokenIssuer;
use App\Library\Domains\Auth\DTOs\AuthenticatedSession;
use App\Library\Domains\Users\Actions\CreateUserAction;
use App\Library\Domains\Users\DTOs\CreateUserData;

/**
 * FR-AUTH-2: public registration creates a member and logs them straight in.
 * The role is not taken from the payload — it is decided here.
 */
final readonly class RegisterUserAction
{
    public function __construct(
        private CreateUserAction $createUser,
        private TokenIssuer $tokens,
    ) {}

    public function __invoke(CreateUserData $data): AuthenticatedSession
    {
        $user = ($this->createUser)($data);

        return new AuthenticatedSession(
            user: $user,
            token: $this->tokens->issue($user),
        );
    }
}
