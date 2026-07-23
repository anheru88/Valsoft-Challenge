<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Enums;

/**
 * FR-BOOK-5: the sortable columns of the catalogue.
 */
enum BookSortField: string
{
    case Title = 'title';
    case PublicationYear = 'publication_year';
    case CreatedAt = 'created_at';
    case AvailableCopies = 'available_copies';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
