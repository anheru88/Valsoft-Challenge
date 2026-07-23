<?php

declare(strict_types=1);

namespace App\Library\Shared\Application;

use Illuminate\Http\Request;

/**
 * FR-LIST-1: page-based pagination with a hard ceiling, so no client can ask
 * the database for an unbounded result set.
 */
final readonly class PaginationParams
{
    private function __construct(
        public int $page,
        public int $perPage,
    ) {}

    public static function make(?int $page, ?int $perPage): self
    {
        $max = (int) config('library.pagination.max_per_page');
        $default = (int) config('library.pagination.default_per_page');

        return new self(
            page: max(1, $page ?? 1),
            perPage: min($max, max(1, $perPage ?? $default)),
        );
    }

    public static function fromRequest(Request $request): self
    {
        return self::make(
            page: $request->has('page') ? $request->integer('page') : null,
            perPage: $request->has('per_page') ? $request->integer('per_page') : null,
        );
    }
}
