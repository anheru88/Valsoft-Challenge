<?php

declare(strict_types=1);

namespace App\Library\Domains\Authors\Enums;

/**
 * FR-AUTHOR-3.
 */
enum AuthorSortField: string
{
    case Name = 'name';
    case CreatedAt = 'created_at';
    case BooksCount = 'books_count';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
