<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Enums;

/**
 * API specification 6.
 */
enum LoanSortField: string
{
    case LoanedAt = 'loaned_at';
    case DueDate = 'due_date';
    case ReturnedAt = 'returned_at';
    case CreatedAt = 'created_at';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
