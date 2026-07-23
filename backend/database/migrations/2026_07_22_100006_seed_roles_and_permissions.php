<?php

declare(strict_types=1);

use App\Library\Domains\Users\Enums\Permission as LibraryPermission;
use App\Library\Domains\Users\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * The roles and permissions of PRD 8.3 are part of the schema, not of the demo
 * data: the application cannot authorize anything without them, and neither can
 * the test suite. Seeding them here means every database that is migrated —
 * production, local, CI — starts with a working matrix.
 *
 * Assigning a capability to a role stays data; this migration only establishes
 * the starting point.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (LibraryPermission::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }

        foreach (UserRole::cases() as $role) {
            Role::findOrCreate($role->value, 'web')->syncPermissions(
                array_map(fn (LibraryPermission $permission): string => $permission->value, LibraryPermission::forRole($role)),
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::query()->whereIn('name', UserRole::values())->delete();
        Permission::query()->whereIn('name', LibraryPermission::values())->delete();
    }
};
