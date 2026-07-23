<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Enums;

/**
 * The capabilities of the permission matrix (PRD 8.3), named.
 *
 * Policies ask for these rather than for a role, so granting a capability to a
 * new role becomes data rather than a code change. The enum keeps the names
 * typed: a typo in a policy is a fatal error, not a silent denial.
 */
enum Permission: string
{
    /** Browse and search the catalogue. */
    case ViewCatalog = 'catalog.view';

    /** Create, edit and delete books, authors and categories. */
    case ManageCatalog = 'catalog.manage';

    /** Check books out and in. */
    case ManageLoans = 'loans.manage';

    /** Read any member's loans, not only one's own. */
    case ViewAnyLoan = 'loans.view-any';

    /** Read a user account (desk service). */
    case ViewUser = 'users.view';

    /** List every user account. */
    case ViewAnyUser = 'users.view-any';

    /** Create accounts with any role. */
    case CreateAnyUser = 'users.create-any';

    /** Create member accounts only. */
    case CreateMember = 'users.create-member';

    /** Edit, delete, and change the role or status of any account. */
    case ManageUsers = 'users.manage';

    case ViewDashboard = 'dashboard.view';

    case ViewReports = 'reports.view';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * The matrix itself: which capabilities each role carries.
     *
     * @return list<self>
     */
    public static function forRole(UserRole $role): array
    {
        $member = [self::ViewCatalog];

        $librarian = [
            ...$member,
            self::ManageCatalog,
            self::ManageLoans,
            self::ViewAnyLoan,
            self::ViewUser,
            self::CreateMember,
            self::ViewDashboard,
        ];

        return match ($role) {
            UserRole::Member => $member,
            UserRole::Librarian => $librarian,
            UserRole::Admin => [
                ...$librarian,
                self::ViewAnyUser,
                self::CreateAnyUser,
                self::ManageUsers,
                self::ViewReports,
            ],
        };
    }
}
