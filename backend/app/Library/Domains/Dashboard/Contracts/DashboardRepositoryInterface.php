<?php

declare(strict_types=1);

namespace App\Library\Domains\Dashboard\Contracts;

use App\Library\Domains\Loans\Models\Loan;
use App\Library\Shared\Application\PaginationParams;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Reporting reads (FR-DASH, FR-RPT). Every method here is expected to be one
 * aggregate query: the dashboard must never walk rows in PHP (FR-DASH-3).
 */
interface DashboardRepositoryInterface
{
    /**
     * @return array<string, int>
     */
    public function kpis(): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function recentActivity(int $limit): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function popularAuthors(int $days, int $limit): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function booksByCategory(): array;

    /**
     * @return array{labels: list<string>, loans: list<int>, returns: list<int>}
     */
    public function monthlyStats(int $months): array;

    /**
     * @return LengthAwarePaginator<int, Loan>
     */
    public function overdueLoans(PaginationParams $pagination, string $direction): LengthAwarePaginator;

    /**
     * @return list<array<string, mixed>>
     */
    public function mostBorrowed(?string $from, ?string $to, int $limit): array;
}
