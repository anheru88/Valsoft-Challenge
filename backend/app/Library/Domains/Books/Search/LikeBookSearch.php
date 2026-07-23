<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Search;

use App\Library\Domains\Books\Contracts\BookSearchInterface;
use App\Library\Domains\Books\DTOs\BookFilters;
use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Books\ValueObjects\Isbn;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * The portable implementation: indexed prefix matching plus contains-matching
 * on the long text fields.
 *
 * Used wherever FULLTEXT is unavailable (SQLite in the test suite and local
 * development). Behaviourally close enough that the contract's tests hold for
 * both, but without relevance ranking — which is exactly why MariaDB gets the
 * FULLTEXT implementation.
 */
final class LikeBookSearch implements BookSearchInterface
{
    public function __construct(
        private readonly BookQueryFilters $filters,
        private readonly MatchAnnotator $annotator,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Book>
     */
    public function search(BookFilters $filters): LengthAwarePaginator
    {
        $query = $this->filters->withListRelations(Book::query());

        $term = $filters->q;

        if ($term !== null && $term !== '') {
            // ISBN-shaped input goes to the unique index directly (RFC 11).
            if (Isbn::looksLikeIsbn($term)) {
                // Matched against the canonical stored form, so an ISBN-10 finds
                // the book that was catalogued as its ISBN-13 (BR-BOOK-1).
                $query->where('isbn', Isbn::canonical($term));
            } else {
                $query->where(fn (Builder $scoped) => $scoped
                    ->where('title', 'like', $term.'%')
                    ->orWhere('title', 'like', '%'.$term.'%')
                    ->orWhere('description', 'like', '%'.$term.'%')
                    ->orWhere('publisher', 'like', '%'.$term.'%')
                    ->orWhereHas('authors', fn (Builder $authors) => $authors->where('name', 'like', $term.'%'))
                    ->orWhereHas('categories', fn (Builder $categories) => $categories->where('name', 'like', $term.'%')));
            }
        }

        $this->filters->apply($query, $filters);
        $query->orderBy($filters->sort->field, $filters->sort->direction);

        $results = $query->paginate(perPage: $filters->pagination->perPage, page: $filters->pagination->page);

        if ($term !== null && $term !== '') {
            $results->getCollection()->transform(fn (Book $book): Book => $this->annotator->annotate($book, $term));
        }

        return $results;
    }
}
