<?php

declare(strict_types=1);

namespace App\Library\Shared\Infrastructure;

use App\Library\Shared\Application\AuditLogger;
use App\Library\Shared\Domain\DomainEvent;

/**
 * One listener for every domain event: each new event is audited by virtue of
 * implementing DomainEvent, with no registration step to forget.
 *
 * Synchronous on purpose for the MVP — the audit entry belongs to the request
 * that caused it. Moving it to the queue is a one-line change once Redis is on.
 */
final readonly class RecordDomainEventAudit
{
    public function __construct(private AuditLogger $audit) {}

    public function handle(DomainEvent $event): void
    {
        $this->audit->record($event->eventName(), $event->auditContext());
    }
}
