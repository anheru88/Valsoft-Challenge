<?php

declare(strict_types=1);

namespace App\Library\Domains\Auth\Infrastructure;

use App\Library\Domains\Auth\Contracts\TokenIssuer;
use App\Library\Domains\Users\Models\User;

/**
 * Sanctum personal access tokens (FR-AUTH-1). Revocation is a database delete,
 * so a revoked token stops working on the next request rather than at some
 * expiry in the future.
 *
 * The revoke methods run on routes behind auth:sanctum, where the request was
 * authenticated by a real stored token.
 */
final class SanctumTokenIssuer implements TokenIssuer
{
    public function issue(User $user, string $name = 'api'): string
    {
        return $user->createToken($name)->plainTextToken;
    }

    public function revokeCurrent(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function revokeOthers(User $user): void
    {
        $user->tokens()
            ->whereKeyNot($user->currentAccessToken()->getKey())
            ->delete();
    }

    public function revokeAll(User $user): void
    {
        $user->tokens()->delete();
    }
}
