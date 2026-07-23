<?php

declare(strict_types=1);

namespace App\Library\Domains\Dashboard\Repositories;

use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Library\Domains\Loans\Models\Loan;
use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Domains\Users\Models\User;
use App\Library\Shared\Application\PaginationParams;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

final class EloquentDashboardRepository implements DashboardRepositoryInterface
{
    /**
     * @return array<string, int>
     */
    public function kpis(): array
    {
        $today = CarbonImmutable::today();
        $monthStart = $today->startOfMonth();

        // Two aggregate rows and two counts, rather than one query per metric.
        /** @var object{total_books: int, total_copies: int, available_copies: int, books_added_this_month: int} $catalog */
        $catalog = Book::query()
            ->selectRaw('COUNT(*) as total_books')
            ->selectRaw('COALESCE(SUM(total_copies), 0) as total_copies')
            ->selectRaw('COALESCE(SUM(available_copies), 0) as available_copies')
            ->selectRaw('SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as books_added_this_month', [$monthStart])
            ->first();

        /** @var object{borrowed_now: int, overdue_now: int, loans_this_month: int, returns_this_month: int} $circulation */
        $circulation = Loan::query()
            ->selectRaw('SUM(CASE WHEN returned_at IS NULL THEN 1 ELSE 0 END) as borrowed_now')
            ->selectRaw('SUM(CASE WHEN returned_at IS NULL AND due_date < ? THEN 1 ELSE 0 END) as overdue_now', [$today->toDateString()])
            ->selectRaw('SUM(CASE WHEN loaned_at >= ? THEN 1 ELSE 0 END) as loans_this_month', [$monthStart->toDateString()])
            ->selectRaw('SUM(CASE WHEN returned_at >= ? THEN 1 ELSE 0 END) as returns_this_month', [$monthStart])
            ->first();

        return [
            'total_books' => (int) $catalog->total_books,
            'total_copies' => (int) $catalog->total_copies,
            'available_copies' => (int) $catalog->available_copies,
            'borrowed_now' => (int) $circulation->borrowed_now,
            'overdue_now' => (int) $circulation->overdue_now,
            'total_members' => User::query()->role(UserRole::Member->value)->where('is_active', true)->count(),
            'books_added_this_month' => (int) $catalog->books_added_this_month,
            'loans_this_month' => (int) $circulation->loans_this_month,
            'returns_this_month' => (int) $circulation->returns_this_month,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentActivity(int $limit): array
    {
        // Derived from the loan rows themselves: a loan that has been returned
        // contributes both events, each carrying its own timestamp.
        // A soft deleted user or book leaves its relation null, which the
        // summaries below tolerate rather than dropping the entry.
        $loans = Loan::query()
            ->with(['user:id,name', 'book:id,title'])
            ->latest('created_at')
            ->limit($limit)
            ->get();

        $activity = [];

        foreach ($loans as $loan) {
            $activity[] = [
                'type' => 'loan_created',
                'occurred_at' => $loan->created_at?->toIso8601ZuluString(),
                'summary' => sprintf('%s borrowed "%s"', $loan->user->name ?? 'A member', $loan->book->title ?? 'a book'),
                'book' => $loan->book === null ? null : ['id' => $loan->book->id, 'title' => $loan->book->title],
                'user' => $loan->user === null ? null : ['id' => $loan->user->id, 'name' => $loan->user->name],
            ];

            if ($loan->returned_at !== null) {
                $activity[] = [
                    'type' => 'loan_returned',
                    'occurred_at' => $loan->returned_at->toIso8601ZuluString(),
                    'summary' => sprintf('%s returned "%s"', $loan->user->name ?? 'A member', $loan->book->title ?? 'a book'),
                    'book' => $loan->book === null ? null : ['id' => $loan->book->id, 'title' => $loan->book->title],
                    'user' => $loan->user === null ? null : ['id' => $loan->user->id, 'name' => $loan->user->name],
                ];
            }
        }

        usort($activity, fn (array $a, array $b): int => ($b['occurred_at'] ?? '') <=> ($a['occurred_at'] ?? ''));

        return array_slice($activity, 0, $limit);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function popularAuthors(int $days, int $limit): array
    {
        $since = CarbonImmutable::today()->subDays($days)->toDateString();

        /** @var list<array<string, mixed>> $rows */
        $rows = DB::table('authors')
            ->join('author_book', 'authors.id', '=', 'author_book.author_id')
            ->join('loans', 'loans.book_id', '=', 'author_book.book_id')
            // Compared as a date: the column carries a time component on some
            // drivers, and a plain string comparison would exclude the boundary day.
            ->whereDate('loans.loaned_at', '>=', $since)
            ->groupBy('authors.id', 'authors.name')
            ->orderByDesc('loans_count')
            ->limit($limit)
            ->get([
                'authors.id',
                'authors.name',
                DB::raw('COUNT(loans.id) as loans_count'),
            ])
            ->map(fn (object $row): array => [
                'author' => ['id' => (int) $row->id, 'name' => $row->name],
                'loans_count' => (int) $row->loans_count,
            ])
            ->all();

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function booksByCategory(): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = DB::table('categories')
            ->leftJoin('book_category', 'categories.id', '=', 'book_category.category_id')
            // Soft deleted books must not inflate a category's count.
            ->leftJoin('books', fn (JoinClause $join) => $join
                ->on('books.id', '=', 'book_category.book_id')
                ->whereNull('books.deleted_at'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('books_count')
            ->get([
                'categories.id',
                'categories.name',
                DB::raw('COUNT(books.id) as books_count'),
            ])
            ->map(fn (object $row): array => [
                'category' => ['id' => (int) $row->id, 'name' => $row->name],
                'books_count' => (int) $row->books_count,
            ])
            ->all();

        return $rows;
    }

    /**
     * @return array{labels: list<string>, loans: list<int>, returns: list<int>}
     */
    public function monthlyStats(int $months): array
    {
        $start = CarbonImmutable::today()->startOfMonth()->subMonths($months - 1);

        $loanBuckets = $this->countByMonth('loans', 'loaned_at', $start);
        $returnBuckets = $this->countByMonth('loans', 'returned_at', $start);

        $labels = [];
        $loans = [];
        $returns = [];

        for ($offset = 0; $offset < $months; $offset++) {
            $label = $start->addMonths($offset)->format('Y-m');
            $labels[] = $label;
            $loans[] = $loanBuckets[$label] ?? 0;
            $returns[] = $returnBuckets[$label] ?? 0;
        }

        return ['labels' => $labels, 'loans' => $loans, 'returns' => $returns];
    }

    /**
     * @return LengthAwarePaginator<int, Loan>
     */
    public function overdueLoans(PaginationParams $pagination, string $direction): LengthAwarePaginator
    {
        return Loan::query()
            ->with(['user:id,name,email', 'book:id,title,isbn'])
            ->whereNull('returned_at')
            ->whereDate('due_date', '<', CarbonImmutable::today())
            // Most overdue first means oldest due date first.
            ->orderBy('due_date', $direction === 'desc' ? 'asc' : 'desc')
            ->paginate(perPage: $pagination->perPage, page: $pagination->page);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function mostBorrowed(?string $from, ?string $to, int $limit): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = DB::table('books')
            ->join('loans', 'loans.book_id', '=', 'books.id')
            ->whereNull('books.deleted_at')
            ->when($from !== null, fn (QueryBuilder $query) => $query->whereDate('loans.loaned_at', '>=', $from))
            ->when($to !== null, fn (QueryBuilder $query) => $query->whereDate('loans.loaned_at', '<=', $to))
            ->groupBy('books.id', 'books.title', 'books.isbn')
            ->orderByDesc('loans_count')
            ->limit($limit)
            ->get([
                'books.id',
                'books.title',
                'books.isbn',
                DB::raw('COUNT(loans.id) as loans_count'),
            ])
            ->map(fn (object $row): array => [
                'book' => ['id' => (int) $row->id, 'title' => $row->title, 'isbn' => $row->isbn],
                'loans_count' => (int) $row->loans_count,
            ])
            ->all();

        return $rows;
    }

    /**
     * One GROUP BY per series. The month expression is driver-specific, which is
     * the only place the reporting queries are not portable.
     *
     * @return array<string, int>
     */
    private function countByMonth(string $table, string $column, CarbonImmutable $since): array
    {
        $expression = $this->monthExpression($column);

        $buckets = [];

        $rows = DB::table($table)
            ->whereNotNull($column)
            ->where($column, '>=', $since)
            ->selectRaw($expression.' as month, COUNT(*) as total')
            ->groupBy(DB::raw($expression))
            ->get();

        foreach ($rows as $row) {
            $buckets[(string) $row->month] = (int) $row->total;
        }

        return $buckets;
    }

    private function monthExpression(string $column): string
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)
            ? "DATE_FORMAT({$column}, '%Y-%m')"
            : "strftime('%Y-%m', {$column})";
    }
}
