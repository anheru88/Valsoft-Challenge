<?php

declare(strict_types=1);

use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Users\Enums\Permission as LibraryPermission;
use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Domains\Users\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('ships the three roles and the whole capability catalogue', function () {
    expect(Role::query()->pluck('name')->sort()->values()->all())
        ->toBe(collect(UserRole::values())->sort()->values()->all())
        ->and(Permission::query()->pluck('name')->sort()->values()->all())
        ->toBe(collect(LibraryPermission::values())->sort()->values()->all());
});

it('grants each role exactly the capabilities of the permission matrix', function (UserRole $role) {
    $expected = collect(LibraryPermission::forRole($role))
        ->map(fn (LibraryPermission $permission): string => $permission->value)
        ->sort()
        ->values()
        ->all();

    $granted = Role::findByName($role->value)
        ->permissions
        ->pluck('name')
        ->sort()
        ->values()
        ->all();

    expect($granted)->toBe($expected);
})->with([
    'admin' => [UserRole::Admin],
    'librarian' => [UserRole::Librarian],
    'member' => [UserRole::Member],
]);

it('authorizes by capability, not by role name', function () {
    $member = User::factory()->member()->create();

    // The member cannot curate the catalogue…
    $this->withBearerToken(token($member))
        ->deleteJson('/api/v1/books/'.Book::factory()->create()->id)
        ->assertForbidden();

    // …until the capability is granted directly, with no code change and no
    // role change. This is what the permission tables buy.
    $member->givePermissionTo(LibraryPermission::ManageCatalog->value);

    $this->withBearerToken(token($member->fresh()))
        ->deleteJson('/api/v1/books/'.Book::factory()->create()->id)
        ->assertNoContent();
});

it('lets a new role be composed without touching code', function () {
    $cataloguer = Role::create(['name' => 'cataloguer', 'guard_name' => 'web']);
    $cataloguer->syncPermissions([
        LibraryPermission::ViewCatalog->value,
        LibraryPermission::ManageCatalog->value,
    ]);

    $user = User::factory()->create();
    $user->syncRoles(['cataloguer']);

    $token = token($user->fresh());

    $this->withBearerToken($token)->postJson('/api/v1/authors', ['name' => 'New Author'])->assertCreated();
    // The new role curates the catalogue but runs no circulation.
    $this->withBearerToken($token)->postJson('/api/v1/loans', [
        'user_id' => User::factory()->member()->create()->id,
        'book_id' => Book::factory()->create()->id,
    ])->assertForbidden();
});

it('reports the effective capabilities of the authenticated account', function () {
    $librarian = User::factory()->librarian()->create();

    $response = $this->withBearerToken(token($librarian))->getJson('/api/v1/auth/me')->assertOk();

    expect($response->json('data.roles'))->toBe(['librarian'])
        ->and($response->json('data.permissions'))
        ->toContain(LibraryPermission::ManageLoans->value)
        ->not->toContain(LibraryPermission::ManageUsers->value);
});

it('includes a directly granted capability in the reported permissions', function () {
    $member = User::factory()->member()->create();
    $member->givePermissionTo(LibraryPermission::ViewDashboard->value);

    $this->withBearerToken(token($member->fresh()))->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.roles', ['member'])
        ->assertJsonPath(
            'data.permissions',
            fn (array $permissions): bool => in_array(LibraryPermission::ViewDashboard->value, $permissions, true),
        );

    $this->withBearerToken(token($member->fresh()))->getJson('/api/v1/dashboard')->assertOk();
});

it('revokes the tokens of a user whose role changed, so stale privileges cannot persist', function () {
    $admin = User::factory()->admin()->create();
    $librarian = User::factory()->librarian()->create();
    $staleToken = token($librarian);

    $this->withBearerToken(token($admin))
        ->putJson("/api/v1/users/{$librarian->id}", ['role' => 'member'])
        ->assertOk()
        ->assertJsonPath('data.roles', ['member']);

    $this->withBearerToken($staleToken)->getJson('/api/v1/auth/me')->assertUnauthorized();
    expect($librarian->fresh()->can(LibraryPermission::ManageCatalog->value))->toBeFalse();
});
