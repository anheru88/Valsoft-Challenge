<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Enums;

/**
 * Derived loan status (FR-LOAN-3). Never persisted: it is a function of
 * returned_at and due_date, so it cannot go stale.
 */
enum LoanStatus: string
{
    case Active = 'active';
    case Overdue = 'overdue';
    case Returned = 'returned';

    public function isOpen(): bool
    {
        return $this !== self::Returned;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
