<?php

declare(strict_types=1);

namespace App\Library\Domains\Authors\DTOs;

use App\Library\Domains\Authors\Enums\AuthorSortField;
use App\Library\Shared\Application\PaginationParams;
use App\Library\Shared\Application\SortParams;
use Illuminate\Http\Request;

final readonly class AuthorFilters
{
    public function __construct(
        public ?string $q,
        public PaginationParams $pagination,
        public SortParams $sort,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            q: $request->filled('q') ? $request->string('q')->trim()->toString() : null,
            pagination: PaginationParams::fromRequest($request),
            sort: SortParams::fromRequest($request, AuthorSortField::values(), AuthorSortField::Name->value),
        );
    }
}
