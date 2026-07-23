<?php

declare(strict_types=1);

namespace App\Library\Shared\Domain;

use RuntimeException;

/**
 * Base class for business-rule violations.
 *
 * These are part of a domain's public API (RFC 7): each subclass carries a
 * stable machine code and a message that is safe to show a user. The default
 * status is 409 — the input was well formed, the world disagrees — and
 * subclasses override it only when the semantics differ (FR-ERR-2).
 */
abstract class DomainException extends RuntimeException
{
    /**
     * Stable code from the registry in the API specification (10).
     */
    abstract public function errorCode(): string;

    public function httpStatus(): int
    {
        return 409;
    }

    /**
     * Machine-readable context clients can act on, e.g. the limit that was hit.
     *
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return [];
    }
}
