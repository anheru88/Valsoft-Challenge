<?php

declare(strict_types=1);

namespace App\Library\Domains\Auth\Actions;

use App\Library\Domains\Auth\Contracts\TokenIssuer;
use App\Library\Domains\Auth\DTOs\AuthenticatedSession;
use App\Library\Domains\Auth\DTOs\LoginData;
use App\Library\Domains\Auth\Exceptions\InvalidCredentialsException;
use App\Library\Domains\Auth\Exceptions\UserInactiveException;
use App\Library\Domains\Users\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Hashing\Hasher;

final readonly class LoginAction
{
    public function __construct(
        private UserRepositoryInterface $users,
        private TokenIssuer $tokens,
        private Hasher $hasher,
    ) {}

    public function __invoke(LoginData $data): AuthenticatedSession
    {
        $user = $this->users->findByEmail($data->email);

        // An unknown email and a wrong password produce the same response, so
        // the endpoint cannot be used to enumerate accounts; the rate limit on
        // this route (FR-AUTH-3) covers the remaining timing side channel.
        if ($user === null || ! $this->hasher->check($data->password, $user->password)) {
            throw new InvalidCredentialsException;
        }

        if (! $user->is_active) {
            throw new UserInactiveException;
        }

        return new AuthenticatedSession(
            user: $user,
            token: $this->tokens->issue($user),
        );
    }
}
