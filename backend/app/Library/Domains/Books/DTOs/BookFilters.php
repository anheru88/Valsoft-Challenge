<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\DTOs;

use App\Library\Domains\Books\Enums\BookSortField;
use App\Library\Shared\Application\PaginationParams;
use App\Library\Shared\Application\SortParams;
use Illuminate\Http\Request;

/**
 * FR-BOOK-5: the catalogue filters. Search reuses this untouched, which is what
 * lets `q` compose with every filter (FR-SRCH-2).
 */
final readonly class BookFilters
{
    public function __construct(
        public ?string $q,
        public ?int $categoryId,
        public ?int $authorId,
        public ?bool $available,
        public ?int $yearFrom,
        public ?int $yearTo,
        public PaginationParams $pagination,
        public SortParams $sort,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            q: $request->filled('q') ? $request->string('q')->trim()->toString() : null,
            categoryId: $request->filled('category_id') ? $request->integer('category_id') : null,
            authorId: $request->filled('author_id') ? $request->integer('author_id') : null,
            available: $request->has('available') ? $request->boolean('available') : null,
            yearFrom: $request->filled('year_from') ? $request->integer('year_from') : null,
            yearTo: $request->filled('year_to') ? $request->integer('year_to') : null,
            pagination: PaginationParams::fromRequest($request),
            sort: SortParams::fromRequest($request, BookSortField::values(), BookSortField::CreatedAt->value),
        );
    }

    public function hasSearchTerm(): bool
    {
        return $this->q !== null && $this->q !== '';
    }
}
