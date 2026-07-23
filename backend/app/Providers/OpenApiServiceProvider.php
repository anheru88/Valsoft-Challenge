<?php

declare(strict_types=1);

namespace App\Providers;

use App\Library\Domains\Users\Models\User;
use App\Support\OpenApi\DescribeErrorEnvelope;
use App\Support\OpenApi\DocumentDomainErrors;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the generated OpenAPI document (API specification 11).
 *
 * The document is derived from the code — FormRequests become request bodies,
 * API Resources become response schemas — so it cannot drift from the
 * implementation the way a hand-written spec does.
 */
class OpenApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Scramble::configure()
            ->withDocumentTransformers(DescribeErrorEnvelope::class)
            ->withOperationTransformers(DocumentDomainErrors::class);

        // Scramble already allows the docs in local. Beyond it, reading the API
        // surface is an administrator's job.
        Gate::define('viewApiDocs', fn (?User $user): bool => $user?->isAdmin() ?? false);
    }
}
