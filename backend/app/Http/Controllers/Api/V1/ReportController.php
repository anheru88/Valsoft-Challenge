<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Library\Domains\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Library\Domains\Loans\Resources\LoanResource;
use App\Library\Shared\Application\PaginationParams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

/**
 * FR-RPT. Administrator-only (API specification 9).
 */
final class ReportController
{
    public function overdue(Request $request, DashboardRepositoryInterface $dashboard): AnonymousResourceCollection
    {
        Gate::authorize('view-reports');

        return LoanResource::collection($dashboard->overdueLoans(
            PaginationParams::fromRequest($request),
            $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc',
        ));
    }

    public function mostBorrowed(Request $request, DashboardRepositoryInterface $dashboard): JsonResponse
    {
        Gate::authorize('view-reports');

        $limit = $request->has('limit') ? max(1, min(100, $request->integer('limit'))) : 10;

        return response()->json([
            'data' => $dashboard->mostBorrowed(
                from: $request->filled('from') ? $request->string('from')->toString() : null,
                to: $request->filled('to') ? $request->string('to')->toString() : null,
                limit: $limit,
            ),
        ]);
    }
}
