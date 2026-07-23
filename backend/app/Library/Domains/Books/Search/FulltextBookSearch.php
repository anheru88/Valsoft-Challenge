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
 * The MariaDB implementation (RFC 11).
 *
 * Three tiers, in order of precision: an ISBN-shaped query is an exact lookup
 * on the unique index; otherwise the FULLTEXT index over title, description and
 * publisher supplies a relevance score, unioned with indexed prefix matches on
 * author and category names. Relevance orders the results unless the client
 * asked for an explicit sort.
 */
final class FulltextBookSearch implements BookSearchInterface
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
        $orderedByRelevance = false;

        if ($term !== null && $term !== '') {
            if (Isbn::looksLikeIsbn($term)) {
                // Matched against the canonical stored form, so an ISBN-10 finds
                // the book that was catalogued as its ISBN-13 (BR-BOOK-1).
                $query->where('isbn', Isbn::canonical($term));
            } else {
                $query->selectRaw(
                    'books.*, MATCH(title, description, publisher) AGAINST (? IN NATURAL LANGUAGE MODE) as relevance',
                    [$term],
                );

                $query->where(fn (Builder $scoped) => $scoped
                    ->whereRaw('MATCH(title, description, publisher) AGAINST (? IN NATURAL LANGUAGE MODE)', [$term])
                    ->orWhere('title', 'like', $term.'%')
                    ->orWhereHas('authors', fn (Builder $authors) => $authors->where('name', 'like', $term.'%'))
                    ->orWhereHas('categories', fn (Builder $categories) => $categories->where('name', 'like', $term.'%')));

                $orderedByRelevance = ! $this->hasExplicitSort();
            }
        }

        $this->filters->apply($query, $filters);

        $orderedByRelevance
            ? $query->orderByDesc('relevance')
            : $query->orderBy($filters->sort->field, $filters->sort->direction);

        $results = $query->paginate(perPage: $filters->pagination->perPage, page: $filters->pagination->page);

        if ($term !== null && $term !== '') {
            $results->getCollection()->transform(fn (Book $book): Book => $this->annotator->annotate($book, $term));
        }

        return $results;
    }

    /**
     * FR-SRCH-2: relevance wins when `q` is present, unless a sort was asked for.
     */
    private function hasExplicitSort(): bool
    {
        return request()->filled('sort');
    }
}
