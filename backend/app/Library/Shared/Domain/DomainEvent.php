<?php

declare(strict_types=1);

namespace App\Library\Shared\Domain;

/**
 * Marker for facts a domain publishes after a use-case succeeds.
 *
 * Events are how side effects (audit trail today; cache invalidation and
 * notifications later) stay out of the use-case itself (RFC 4).
 */
interface DomainEvent
{
    /**
     * Stable dotted name, e.g. `loan.created`.
     */
    public function eventName(): string;

    /**
     * The who/what/when the audit trail records (RFC 8).
     *
     * @return array<string, mixed>
     */
    public function auditContext(): array;
}
