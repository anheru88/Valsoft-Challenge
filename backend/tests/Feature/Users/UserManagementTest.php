<?php

declare(strict_types=1);

use App\Library\Domains\Loans\Models\Loan;
use App\Library\Domains\Users\Models\User;

it('lets an administrator list users with the standard envelope', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->member()->count(3)->create();

    $this->withBearerToken(token($admin))->getJson('/api/v1/users?per_page=2')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'name', 'email', 'roles', 'permissions', 'is_active', 'active_loans_count']],
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            'links' => ['first', 'last', 'prev', 'next'],
        ])
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 4);
});

it('keeps the user list out of reach of librarians and members', function (string $role) {
    $actor = User::factory()->{$role}()->create();

    $this->withBearerToken(token($actor))->getJson('/api/v1/users')
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');
})->with(['librarian', 'member']);

it('filters and sorts the user list', function () {
    $admin = User::factory()->admin()->create(['name' => 'Alicia']);
    User::factory()->librarian()->create(['name' => 'Luis']);
    User::factory()->member()->inactive()->create(['name' => 'Marta']);

    $this->withBearerToken(token($admin))->getJson('/api/v1/users?role=librarian')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Luis');

    $this->withBearerToken(token($admin))->getJson('/api/v1/users?is_active=0')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Marta');

    $this->withBearerToken(token($admin))->getJson('/api/v1/users?sort=name&direction=desc')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Marta');
});

it('rejects a sort field that is not on the whitelist', function () {
    $admin = User::factory()->admin()->create();

    $this->withBearerToken(token($admin))->getJson('/api/v1/users?sort=password')
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');
});

it('lets an administrator create a user with any role', function () {
    $admin = User::factory()->admin()->create();

    $this->withBearerToken(token($admin))->postJson('/api/v1/users', [
        'name' => 'New Librarian',
        'email' => 'new@librarium.test',
        'password' => 's3curePass',
        'role' => 'librarian',
    ])->assertCreated()
        ->assertJsonPath('data.roles', ['librarian'])
        // The effective capabilities travel with the account, so a client can
        // hide what it cannot do.
        ->assertJsonPath('data.permissions', fn (array $permissions): bool => in_array('catalog.manage', $permissions, true)
            && ! in_array('users.manage', $permissions, true))
        ->assertHeader('Location');
});

it('lets a librarian create members only', function () {
    $librarian = User::factory()->librarian()->create();

    $this->withBearerToken(token($librarian))->postJson('/api/v1/users', [
        'name' => 'New Member',
        'email' => 'member@librarium.test',
        'password' => 's3curePass',
        'role' => 'member',
    ])->assertCreated();

    $this->withBearerToken(token($librarian))->postJson('/api/v1/users', [
        'name' => 'New Admin',
        'email' => 'admin2@librarium.test',
        'password' => 's3curePass',
        'role' => 'admin',
    ])->assertForbidden();
});

it('lets a member edit their own profile but not their role', function () {
    $member = User::factory()->member()->create();

    $this->withBearerToken(token($member))->putJson("/api/v1/users/{$member->id}", ['name' => 'Renamed'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Renamed');

    $this->withBearerToken(token($member))->putJson("/api/v1/users/{$member->id}", ['role' => 'admin'])
        ->assertForbidden();

    expect($member->fresh()->isAdmin())->toBeFalse();
});

it('refuses to edit somebody else as a member', function () {
    $member = User::factory()->member()->create();
    $other = User::factory()->member()->create();

    $this->withBearerToken(token($member))->putJson("/api/v1/users/{$other->id}", ['name' => 'Hijacked'])
        ->assertForbidden();
});

it('revokes the tokens of a user whose role changed', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $memberToken = token($member);

    $this->withBearerToken(token($admin))->putJson("/api/v1/users/{$member->id}", ['role' => 'librarian'])
        ->assertOk()
        ->assertJsonPath('data.roles', ['librarian']);

    $this->withBearerToken($memberToken)->getJson('/api/v1/auth/me')->assertUnauthorized();
});

it('deactivates a user and locks them out', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $memberToken = token($member);

    $this->withBearerToken(token($admin))->patchJson("/api/v1/users/{$member->id}/status", ['is_active' => false])
        ->assertOk()
        ->assertJsonPath('data.is_active', false);

    $this->withBearerToken($memberToken)->getJson('/api/v1/auth/me')->assertUnauthorized();
});

it('soft deletes a user so their loan history survives', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    Loan::factory()->returned()->create(['user_id' => $member->id]);

    $this->withBearerToken(token($admin))->deleteJson("/api/v1/users/{$member->id}")
        ->assertNoContent();

    $this->assertSoftDeleted('users', ['id' => $member->id]);
    $this->assertDatabaseHas('loans', ['user_id' => $member->id]);
});

it('refuses to delete a member who still holds books', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    Loan::factory()->create(['user_id' => $member->id]);

    $this->withBearerToken(token($admin))->deleteJson("/api/v1/users/{$member->id}")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'USER_HAS_ACTIVE_LOANS')
        ->assertJsonPath('error.details.active_loans', 1);

    $this->assertNotSoftDeleted('users', ['id' => $member->id]);
});

it('protects the last active administrator', function () {
    $admin = User::factory()->admin()->create();

    $this->withBearerToken(token($admin))->deleteJson("/api/v1/users/{$admin->id}")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'LAST_ADMIN_PROTECTED');

    $this->withBearerToken(token($admin))->putJson("/api/v1/users/{$admin->id}", ['role' => 'member'])
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'LAST_ADMIN_PROTECTED');

    $this->withBearerToken(token($admin))->patchJson("/api/v1/users/{$admin->id}/status", ['is_active' => false])
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'LAST_ADMIN_PROTECTED');
});

it('allows demoting an administrator while another one remains', function () {
    $admin = User::factory()->admin()->create();
    $secondAdmin = User::factory()->admin()->create();

    $this->withBearerToken(token($admin))->putJson("/api/v1/users/{$secondAdmin->id}", ['role' => 'member'])
        ->assertOk()
        ->assertJsonPath('data.roles', ['member']);
});

it('lets a librarian read a user for desk service', function () {
    $librarian = User::factory()->librarian()->create();
    $member = User::factory()->member()->create();

    $this->withBearerToken(token($librarian))->getJson("/api/v1/users/{$member->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $member->id);
});

it('hides other members from a member', function () {
    $member = User::factory()->member()->create();
    $other = User::factory()->member()->create();

    $this->withBearerToken(token($member))->getJson("/api/v1/users/{$other->id}")
        ->assertForbidden();

    $this->withBearerToken(token($member))->getJson("/api/v1/users/{$member->id}")
        ->assertOk();
});
