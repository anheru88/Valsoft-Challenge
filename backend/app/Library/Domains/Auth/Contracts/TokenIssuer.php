<?php

declare(strict_types=1);

namespace App\Library\Domains\Auth\Contracts;

use App\Library\Domains\Users\Models\User;

/**
 * Token minting is an authentication mechanism, not a business rule, so the
 * actions depend on this contract and Sanctum stays at the infrastructure edge
 * (RFC 6). Swapping the mechanism would not touch a single use-case.
 */
interface TokenIssuer
{
    /**
     * @return string the plain-text bearer token, shown to the client once
     */
    public function issue(User $user, string $name = 'api'): string;

    /**
     * Revokes the token authenticating the current request (FR-AUTH-4).
     */
    public function revokeCurrent(User $user): void;

    /**
     * FR-AUTH-7: a password change invalidates every other session.
     */
    public function revokeOthers(User $user): void;

    /**
     * BR-USER-5: deactivation, deletion and role changes cut every session the
     * user has, immediately.
     */
    public function revokeAll(User $user): void;
}
