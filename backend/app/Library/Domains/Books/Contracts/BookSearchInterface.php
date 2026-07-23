<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Contracts;

use App\Library\Domains\Books\DTOs\BookFilters;
use App\Library\Domains\Books\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * The search seam of ADR-6.
 *
 * The MVP ships database-native implementations behind this contract. Moving to
 * Meilisearch when the catalogue outgrows FULLTEXT — the documented trigger is
 * roughly 250k titles or complaints about typo tolerance — is a new
 * implementation and a binding change, with no controller touched.
 */
interface BookSearchInterface
{
    /**
     * @return LengthAwarePaginator<int, Book>
     */
    public function search(BookFilters $filters): LengthAwarePaginator;
}
