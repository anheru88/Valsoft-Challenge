<?php

declare(strict_types=1);

namespace App\Providers;

use App\Library\Domains\Auth\Contracts\TokenIssuer;
use App\Library\Domains\Auth\Infrastructure\SanctumTokenIssuer;
use App\Library\Domains\Authors\Contracts\AuthorRepositoryInterface;
use App\Library\Domains\Authors\Models\Author;
use App\Library\Domains\Authors\Policies\AuthorPolicy;
use App\Library\Domains\Authors\Repositories\EloquentAuthorRepository;
use App\Library\Domains\Books\Contracts\BookRepositoryInterface;
use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Books\Policies\BookPolicy;
use App\Library\Domains\Books\Repositories\EloquentBookRepository;
use App\Library\Domains\Categories\Contracts\CategoryRepositoryInterface;
use App\Library\Domains\Categories\Models\Category;
use App\Library\Domains\Categories\Policies\CategoryPolicy;
use App\Library\Domains\Categories\Repositories\EloquentCategoryRepository;
use App\Library\Domains\Loans\Contracts\LoanRepositoryInterface;
use App\Library\Domains\Loans\Repositories\EloquentLoanRepository;
use App\Library\Domains\Users\Contracts\UserRepositoryInterface;
use App\Library\Domains\Users\Models\User;
use App\Library\Domains\Users\Policies\UserPolicy;
use App\Library\Domains\Users\Repositories\EloquentUserRepository;
use App\Library\Shared\Application\AuditLogger;
use App\Library\Shared\Application\Clock;
use App\Library\Shared\Application\TransactionRunner;
use App\Library\Shared\Domain\DomainEvent;
use App\Library\Shared\Infrastructure\DatabaseTransactionRunner;
use App\Library\Shared\Infrastructure\LogAuditLogger;
use App\Library\Shared\Infrastructure\RecordDomainEventAudit;
use App\Library\Shared\Infrastructure\SystemClock;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->app->bind(TokenIssuer::class, SanctumTokenIssuer::class);

        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(LoanRepositoryInterface::class, EloquentLoanRepository::class);
        $this->app->bind(BookRepositoryInterface::class, EloquentBookRepository::class);
        $this->app->bind(AuthorRepositoryInterface::class, EloquentAuthorRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
    }

    public function boot(): void
    {
        // Listening on the interface means every domain event is audited by the
        // fact of implementing it (RFC 8).
        Event::listen(DomainEvent::class, RecordDomainEventAudit::class);

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Book::class, BookPolicy::class);
        Gate::policy(Author::class, AuthorPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);

        $this->registerRateLimiters();

        // Eager loads are declared explicitly by repositories; a lazy load in
        // development is a bug report, not a silent extra query (RFC 10).
        Model::preventLazyLoading(! $this->app->isProduction());
    }

    private function registerRateLimiters(): void
    {
        // FR-AUTH-3: keyed by email and IP together, so one attacker cannot
        // lock a victim out by burning their quota from elsewhere.
        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(5)
            ->by(strtolower($request->string('email')->toString()).'|'.$request->ip()));

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)
            ->by($request->user()?->getAuthIdentifier() ?? $request->ip()));
    }
}
