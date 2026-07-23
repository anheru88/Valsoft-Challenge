<?php

declare(strict_types=1);

use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Domains\Users\Models\User;

it('registers a member and returns a bearer token', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Marta Ruiz',
        'email' => 'Marta@Example.com',
        'password' => 's3curePass',
        'password_confirmation' => 's3curePass',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.user.role', 'member')
        ->assertJsonPath('data.user.email', 'marta@example.com')
        ->assertJsonStructure(['data' => ['token', 'token_type', 'user' => ['id', 'name', 'email', 'role', 'is_active']]]);

    expect(User::where('email', 'marta@example.com')->first()->role)->toBe(UserRole::Member);
});

it('never lets a registration choose its own role', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Would Be Admin',
        'email' => 'sneaky@example.com',
        'password' => 's3curePass',
        'password_confirmation' => 's3curePass',
        'role' => 'admin',
    ])->assertCreated()->assertJsonPath('data.user.role', 'member');
});

it('rejects weak passwords and duplicate emails', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Weak',
        'email' => 'taken@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED')
        ->assertJsonStructure(['error' => ['details' => ['errors' => ['email', 'password']]]]);
});

it('logs in with valid credentials', function () {
    User::factory()->create(['email' => 'marta@example.com', 'password' => 's3curePass']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'marta@example.com',
        'password' => 's3curePass',
    ])->assertOk()->assertJsonPath('data.user.email', 'marta@example.com');
});

it('answers a wrong password and an unknown email identically', function () {
    User::factory()->create(['email' => 'marta@example.com', 'password' => 's3curePass']);

    $wrongPassword = $this->postJson('/api/v1/auth/login', [
        'email' => 'marta@example.com',
        'password' => 'not-the-password',
    ]);

    $unknownEmail = $this->postJson('/api/v1/auth/login', [
        'email' => 'nobody@example.com',
        'password' => 'not-the-password',
    ]);

    $wrongPassword->assertStatus(422)->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
    $unknownEmail->assertStatus(422)->assertJsonPath('error.code', 'INVALID_CREDENTIALS');

    expect($wrongPassword->json('error.message'))->toBe($unknownEmail->json('error.message'));
});

it('refuses to log in a deactivated account', function () {
    User::factory()->inactive()->create(['email' => 'gone@example.com', 'password' => 's3curePass']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'gone@example.com',
        'password' => 's3curePass',
    ])->assertForbidden()->assertJsonPath('error.code', 'USER_INACTIVE');
});

it('throttles login attempts after five tries a minute', function () {
    User::factory()->create(['email' => 'marta@example.com', 'password' => 's3curePass']);

    foreach (range(1, 5) as $ignored) {
        $this->postJson('/api/v1/auth/login', ['email' => 'marta@example.com', 'password' => 'wrong']);
    }

    $this->postJson('/api/v1/auth/login', ['email' => 'marta@example.com', 'password' => 'wrong'])
        ->assertStatus(429)
        ->assertJsonPath('error.code', 'RATE_LIMITED')
        ->assertHeader('Retry-After');
});

it('returns the authenticated user with their active loan count', function () {
    $user = User::factory()->member()->create();

    $this->withBearerToken(token($user))->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.active_loans_count', 0);
});

it('revokes only the current token on logout', function () {
    $user = User::factory()->create();
    $first = token($user);
    $second = token($user);

    $this->withBearerToken($first)->postJson('/api/v1/auth/logout')->assertNoContent();

    $this->withBearerToken($first)->getJson('/api/v1/auth/me')->assertUnauthorized();
    $this->withBearerToken($second)->getJson('/api/v1/auth/me')->assertOk();
});

it('changes the password and revokes every other session', function () {
    $user = User::factory()->create(['password' => 'currentPass1']);
    $keptToken = token($user);
    $otherToken = token($user);

    $this->withBearerToken($keptToken)->putJson('/api/v1/auth/password', [
        'current_password' => 'currentPass1',
        'password' => 'brandNewPass2',
        'password_confirmation' => 'brandNewPass2',
    ])->assertNoContent();

    $this->withBearerToken($keptToken)->getJson('/api/v1/auth/me')->assertOk();
    $this->withBearerToken($otherToken)->getJson('/api/v1/auth/me')->assertUnauthorized();

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'brandNewPass2',
    ])->assertOk();
});

it('rejects a password change without the current password', function () {
    $user = User::factory()->create(['password' => 'currentPass1']);

    $this->withBearerToken(token($user))->putJson('/api/v1/auth/password', [
        'current_password' => 'wrongPass1',
        'password' => 'brandNewPass2',
        'password_confirmation' => 'brandNewPass2',
    ])->assertStatus(422)->assertJsonPath('error.code', 'CURRENT_PASSWORD_INVALID');
});

it('locks out a user deactivated mid-session', function () {
    $user = User::factory()->create();
    $token = token($user);

    $user->update(['is_active' => false]);

    $this->withBearerToken($token)->getJson('/api/v1/auth/me')
        ->assertForbidden()
        ->assertJsonPath('error.code', 'USER_INACTIVE');
});
