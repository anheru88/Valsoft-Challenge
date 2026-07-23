<?php

declare(strict_types=1);

namespace App\Providers;

use App\Library\Shared\Application\AuditLogger;
use App\Library\Shared\Application\Clock;
use App\Library\Shared\Application\TransactionRunner;
use App\Library\Shared\Domain\DomainEvent;
use App\Library\Shared\Infrastructure\DatabaseTransactionRunner;
use App\Library\Shared\Infrastructure\LogAuditLogger;
use App\Library\Shared\Infrastructure\RecordDomainEventAudit;
use App\Library\Shared\Infrastructure\SystemClock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the domain's contracts to their infrastructure implementations. This is
 * the only place the inward-pointing dependency rule of RFC 5 is closed: the
 * domain names interfaces, the container supplies the adapters.
 */
class LibraryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Clock::class, SystemClock::class);
        $this->app->bind(TransactionRunner::class, DatabaseTransactionRunner::class);
        $this->app->bind(AuditLogger::class, LogAuditLogger::class);
    }

    public function boot(): void
    {
        // Listening on the interface means every domain event is audited by the
        // fact of implementing it (RFC 8).
        Event::listen(DomainEvent::class, RecordDomainEventAudit::class);

        // Eager loads are declared explicitly by repositories; a lazy load in
        // development is a bug report, not a silent extra query (RFC 10).
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
