<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Enums;

/**
 * FR-USER-1: the sortable columns of the admin user list. Enumerating them is
 * what makes `sort` safe to pass to the query builder (FR-LIST-2).
 */
enum UserSortField: string
{
    case Name = 'name';
    case Email = 'email';
    case CreatedAt = 'created_at';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
