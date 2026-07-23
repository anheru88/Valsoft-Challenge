<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Library\Domains\Books\Contracts\BookRepositoryInterface;
use App\Library\Domains\Books\DTOs\BookFilters;
use App\Library\Domains\Books\Enums\BookSortField;
use App\Library\Domains\Books\Resources\BookResource;
use App\Library\Domains\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Library\Shared\Application\PaginationParams;
use App\Library\Shared\Application\SortParams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

/**
 * FR-DASH. Every payload is chart-ready and comes from aggregate SQL, so the
 * dashboard is a fixed number of queries regardless of catalogue size.
 */
final class DashboardController
{
    public function index(DashboardRepositoryInterface $dashboard): JsonResponse
    {
        Gate::authorize('view-dashboard');

        return response()->json(['data' => $dashboard->kpis()]);
    }

    public function recentActivity(Request $request, DashboardRepositoryInterface $dashboard): JsonResponse
    {
        Gate::authorize('view-dashboard');

        return response()->json([
            'data' => $dashboard->recentActivity($this->boundedLimit($request, default: 20, max: 50)),
        ]);
    }

    public function popularAuthors(Request $request, DashboardRepositoryInterface $dashboard): JsonResponse
    {
        Gate::authorize('view-dashboard');

        return response()->json([
            'data' => $dashboard->popularAuthors(
                days: $this->windowInDays($request),
                limit: $this->boundedLimit($request, default: 5, max: 50),
            ),
        ]);
    }

    public function recentBooks(Request $request, BookRepositoryInterface $books): AnonymousResourceCollection
    {
        Gate::authorize('view-dashboard');

        $limit = $this->boundedLimit($request, default: 10, max: 50);

        $filters = new BookFilters(
            q: null,
            categoryId: null,
            authorId: null,
            available: null,
            yearFrom: null,
            yearTo: null,
            pagination: PaginationParams::make(1, $limit),
            sort: SortParams::make(BookSortField::CreatedAt->value, 'desc', BookSortField::values(), BookSortField::CreatedAt->value),
        );

        return BookResource::collection($books->paginate($filters));
    }

    public function booksByCategory(DashboardRepositoryInterface $dashboard): JsonResponse
    {
        Gate::authorize('view-dashboard');

        return response()->json(['data' => $dashboard->booksByCategory()]);
    }

    public function monthlyStats(Request $request, DashboardRepositoryInterface $dashboard): JsonResponse
    {
        Gate::authorize('view-dashboard');

        $months = max(1, min(36, $request->has('months') ? $request->integer('months') : 12));

        return response()->json(['data' => $dashboard->monthlyStats($months)]);
    }

    /**
     * A window such as `30d`; anything unparseable falls back to 30 days.
     */
    private function windowInDays(Request $request): int
    {
        $window = $request->string('window')->toString();

        if (preg_match('/^(\d+)d$/', $window, $matches) === 1) {
            return max(1, min(365, (int) $matches[1]));
        }

        return 30;
    }

    private function boundedLimit(Request $request, int $default, int $max): int
    {
        if (! $request->has('limit')) {
            return $default;
        }

        return max(1, min($max, $request->integer('limit')));
    }
}
