<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Search;

use App\Library\Domains\Authors\Models\Author;
use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Books\ValueObjects\Isbn;
use App\Library\Domains\Categories\Models\Category;
use Illuminate\Support\Str;

/**
 * Fills in `matched_on` for a search hit (API specification 8).
 *
 * Derived in PHP from relations the query already loaded, so explaining a match
 * costs no extra query.
 */
final class MatchAnnotator
{
    public function annotate(Book $book, string $term): Book
    {
        $book->setAttribute('matched_on', $this->fieldsMatching($book, $term));

        return $book;
    }

    /**
     * @return list<string>
     */
    private function fieldsMatching(Book $book, string $term): array
    {
        $matches = [];

        if (Isbn::looksLikeIsbn($term) && $book->isbn === Isbn::canonical($term)) {
            $matches[] = 'isbn';
        }

        if (Str::contains($book->title, $term, ignoreCase: true)) {
            $matches[] = 'title';
        }

        if ($book->description !== null && Str::contains($book->description, $term, ignoreCase: true)) {
            $matches[] = 'description';
        }

        if ($book->publisher !== null && Str::contains($book->publisher, $term, ignoreCase: true)) {
            $matches[] = 'publisher';
        }

        if ($book->relationLoaded('authors')
            && $book->authors->contains(fn (Author $author): bool => Str::contains($author->name, $term, ignoreCase: true))) {
            $matches[] = 'author';
        }

        if ($book->relationLoaded('categories')
            && $book->categories->contains(fn (Category $category): bool => Str::contains($category->name, $term, ignoreCase: true))) {
            $matches[] = 'category';
        }

        return $matches;
    }
}
