<?php

declare(strict_types=1);

namespace App\Library\Shared\Infrastructure;

use App\Library\Shared\Application\AuditLogger;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Log\LogManager;

/**
 * RFC 8: the audit trail starts as structured `info` log entries carrying who,
 * what and when. This is the seed of a future audit_logs table — swapping the
 * destination is a binding change, not a change to any use-case.
 */
final readonly class LogAuditLogger implements AuditLogger
{
    public function __construct(
        private LogManager $log,
        private Guard $auth,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function record(string $event, array $context): void
    {
        $this->log->info('audit', [
            'event' => $event,
            'actor_id' => $this->auth->id(),
            ...$context,
        ]);
    }
}
