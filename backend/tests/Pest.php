<?php

use App\Library\Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * Feature tests hit the HTTP surface and need a database. Unit tests cover the
 * domain and application layers and must stay database-free (RFC 13) — they
 * boot the container for configuration only.
 */
pest()->extend(TestCase::class)->in('Unit');

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * Issues a real Sanctum token rather than faking the guard, so tests exercise
 * the same authentication path production uses — including revocation.
 */
function token(User $user, string $name = 'test'): string
{
    return $user->createToken($name)->plainTextToken;
}
