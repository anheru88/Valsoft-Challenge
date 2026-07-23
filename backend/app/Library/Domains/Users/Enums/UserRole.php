<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Enums;

/**
 * The three MVP roles (FR-PERM-1). "Staff" is the recurring shorthand in the
 * permission matrix for admin + librarian.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Librarian = 'librarian';
    case Member = 'member';

    public function isStaff(): bool
    {
        return $this !== self::Member;
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
