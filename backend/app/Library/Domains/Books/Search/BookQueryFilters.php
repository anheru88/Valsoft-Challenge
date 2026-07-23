<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Search;

use App\Library\Domains\Books\DTOs\BookFilters;
use App\Library\Domains\Books\Models\Book;
use Illuminate\Database\Eloquent\Builder;

/**
 * The catalogue filters, shared by the list endpoint and by search.
 *
 * FR-SRCH-2 requires `q` to compose with every book filter; keeping the filter
 * translation in one place is what guarantees the two surfaces cannot drift
 * apart.
 */
final class BookQueryFilters
{
    /**
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    public function apply(Builder $query, BookFilters $filters): Builder
    {
        if ($filters->categoryId !== null) {
            $query->whereHas('categories', fn (Builder $categories) => $categories->whereKey($filters->categoryId));
        }

        if ($filters->authorId !== null) {
            $query->whereHas('authors', fn (Builder $authors) => $authors->whereKey($filters->authorId));
        }

        if ($filters->available !== null) {
            $filters->available
                ? $query->where('available_copies', '>', 0)
                : $query->where('available_copies', '=', 0);
        }

        if ($filters->yearFrom !== null) {
            $query->where('publication_year', '>=', $filters->yearFrom);
        }

        if ($filters->yearTo !== null) {
            $query->where('publication_year', '<=', $filters->yearTo);
        }

        return $query;
    }

    /**
     * Eager loads and the loan count, declared once so every book payload costs
     * the same fixed number of queries (RFC 10).
     *
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    public function withListRelations(Builder $query): Builder
    {
        return $query
            ->with(['authors:id,name', 'categories:id,name,slug'])
            ->withCount(['loans as active_loans_count' => fn (Builder $loans) => $loans->whereNull('returned_at')]);
    }
}
