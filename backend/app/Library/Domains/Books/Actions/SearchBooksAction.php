<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Actions;

use App\Library\Domains\Books\Contracts\BookSearchInterface;
use App\Library\Domains\Books\DTOs\BookFilters;
use App\Library\Domains\Books\Exceptions\SearchQueryTooShortException;
use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Books\ValueObjects\Isbn;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class SearchBooksAction
{
    public function __construct(private BookSearchInterface $search) {}

    /**
     * @return LengthAwarePaginator<int, Book>
     */
    public function __invoke(BookFilters $filters): LengthAwarePaginator
    {
        $this->guardQueryLength($filters);

        return $this->search->search($filters);
    }

    /**
     * FR-SRCH-3. An ISBN-shaped query is exempt: it is an exact lookup, not a
     * scan. Filter-only browsing with no `q` at all stays valid.
     */
    private function guardQueryLength(BookFilters $filters): void
    {
        if (! $filters->hasSearchTerm()) {
            return;
        }

        $minimum = (int) config('library.search.min_query_length');
        $term = (string) $filters->q;

        if (mb_strlen($term) >= $minimum || Isbn::looksLikeIsbn($term)) {
            return;
        }

        throw SearchQueryTooShortException::requiring($minimum);
    }
}
